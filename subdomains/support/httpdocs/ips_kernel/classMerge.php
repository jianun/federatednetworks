<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Three Way Merge Class
 * Last Updated: $Date: 2009-02-04 20:03:59 +0000 (Wed, 04 Feb 2009) $ BY $author$
 * </pre>
 *
 * @author 		Matt Mecham
 * @copyright	(c) 2001 - 2010 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 3887 $
 *
 */

class ThreeWayMerge
{
	/**
	 * Original text
	 * For example, template bit from 3.0.5
	 *
	 * @access	private
	 */
	private $_ORIG = array();
	
	/**
	 * New text
	 * For example, template bit from 3.1.0
	 *
	 * @access	private
	 */
	private $_NEW = array();
	
	/**
	 * Custom text
	 * For example, user customized template bit
	 *
	 * @access	private
	 */
	private $_CUSTOM = array();
	
	/**
	 * Debug messages
	 *
	 * @access	public
	 */
	public $DEBUG = array();
	
	public $diffType = 'lite';
	
	/**
	 * Construct
	 *
	 * @param	string		Original Text
	 * @param	string		New Text
	 * @param	string		Custom Text
	 */
	public function __construct( $ORIG, $NEW, $CUSTOM, $mType='lite' )
	{
		$this->_ORIG   = ( ! is_array( $ORIG ) )   ? explode( "\n", str_replace( "\r\n", "\n", trim( $ORIG ) ) ) : $ORIG;
		$this->_NEW    = ( ! is_array( $NEW ) )    ? explode( "\n", str_replace( "\r\n", "\n" , trim( $NEW ) ) ) : $NEW;
		$this->_CUSTOM = ( ! is_array( $CUSTOM ) ) ? explode( "\n", str_replace( "\r\n", "\n" , trim( $CUSTOM ) ) ) : $CUSTOM;
		
		if ( $mType != 'lite' )
		{
			$this->diffType = $mType;
		}
		
		/* Set include path.. */
		@set_include_path( IPS_KERNEL_PATH . 'PEAR/' );
		
		/* OMG.. too many PHP 5 errors under strict standards */
		if ( IN_DEV )
		{
			$oldReportLevel = error_reporting( 0 );
			error_reporting( $oldReportLevel ^ E_STRICT );
		}
		else
		{
			error_reporting( 0 );
		}
		
		/* ugh */
		if ( $this->diffType == 'lite' )
		{
			require_once 'Text/Diff3.php';
			require_once 'Text/Diff/Renderer.php';
			require_once 'Text/Diff/Renderer/inline.php';
		}
		else
		{
			require_once 'Text/Diff.php';
			require_once 'Text/Diff/Renderer.php';
			require_once 'Text/Diff/Renderer/ips3waymerge.php';
		}
	}
	
	/**
	 * Merge
	 *
	 * @access	public
	 * @return	string		Merged string
	 * 
	 * On conflict, will throw an exception with a message of CONFLICT
	 */
	 
	public function merge()
	{
		$this->_addDebugMsg( '---- START--o---', $this->_ORIG );
		$this->_addDebugMsg( '---- START--n---', $this->_NEW );
		$this->_addDebugMsg( '---- START--c---', $this->_CUSTOM );
		
		if ( $this->diffType == 'lite' )
		{
			/* Create the Diff object. */
			$diff = new Text_Diff3( $this->_ORIG, $this->_NEW, $this->_CUSTOM );
			
			/* Output the diff in unified format. */
			return $this->_postProcessQuick( implode( "\n", $diff->mergedOutput( 'XXXNEWXXX', 'XXXCUSTOMXXX' ) ) );
		}
		else
		{
			/* ORIG - NEW */
			$diff = new Text_Diff( 'auto', array( $this->_ORIG, $this->_NEW ) );
			$renderer = new Text_Diff_Renderer_ips3waymerge();
			$orig_new = explode( "\n", trim( $renderer->render($diff) ) );
			
			
			/* ORIG - CUSTOM */
			$diff = new Text_Diff( 'auto', array( $this->_ORIG, $this->_CUSTOM ) );
			$renderer = new Text_Diff_Renderer_ips3waymerge();
			$orig_custom = explode( "\n", trim( $renderer->render($diff) ) );
		
			
			/* Process the arrays */
			$orig_new_map     = $this->_fetchProcessed( $orig_new, 1 );
			$orig_custom_map  = $this->_fetchProcessed( $orig_custom );
			
			/* Add to debug */
			$this->_addDebugMsg( '---- OLD DEFAULT------', $this->_ORIG );
			$this->_addDebugMsg( '---- NEW DEFAULT------', $this->_NEW );
			$this->_addDebugMsg( '---- USER DEFAULT------', $this->_CUSTOM );
			$this->_addDebugMsg( '---- OLD DEFAULT > NEW DEFAULT DIFF------', $orig_new_map );
			$this->_addDebugMsg( '---- OLD DEFAULT > USER CUSTOM DIFF------', $orig_custom_map );
			
			/* Process the thing */
			try
			{
				$return = $this->_processMerge( $orig_new_map, $orig_custom_map );
				
				return $return;
			}
			catch( Exception $e )
			{
				throw new Exception( $e->getMessage() );
			}
		}
	}
	
	/**
	 * Post process merge (quick)
	 *
	 * @access	private
	 * @param	string		Merge text
	 * @return	string		processed text
	 */
	private function _postProcessQuick( $text )
	{
		/* INIT */
		$count = 0;
		//print $text; exit();
		/* MATCH */
		preg_match_all( "#<<<<<<< XXXNEWXXX(.+?)=======(.+?)>>>>>>> XXXCUSTOMXXX#si", $text, $matches );
		
		if ( is_array($matches) AND count($matches) )
		{
			foreach( $matches[1] as $index => $m )
			{	
				/* Yeah, I like readable code and copying and pasting evidently */
				$_all	 = $matches[0][$index];
				$_new    = trim( $matches[1][$index], "\n" );
				$_custom = trim( $matches[2][$index], "\n" );
				
				$_new    = "<ips:conflict id=\"{$count}\">\n<ips:cblock type=\"original\">\n" . $_custom . "\n</ips:cblock>\n<ips:cblock type=\"new\">\n" . $_new . "\n</ips:cblock>\n</ips:conflict>";
					
				$text = str_replace( $_all,  $_new, $text );
				
				$count++;
			}
		}
		
		return $text;
	}
	
	/**
	 * Process merge
	 * Takes the two maps (old-new, old-custom) and merges.
	 * Will throw an error on conflict.
	 *
	 * @access	private
	 * @param	array		old-new map
	 * @param	array		old-custom map
	 * @return	string
	 */
	private function _processMerge( $orig_new_map, $orig_custom_map )
	{
		$len      = count( $this->_ORIG );
		$c        = 0;
		$conflict = 0;
		$final    = array();
		
		while( $c <= $len )
		{
			$left  = $this->_sliceFromLineNumber( $orig_new_map, $c );
			$right = $this->_sliceFromLineNumber( $orig_custom_map, $c );
			
			if ( $left['type'] == 'ok' AND $right['type'] == 'ok' )
			{
				$test1    = $this->_textToAddAtLine( $orig_new_map, $c );
				$test2    = $this->_textToAddAtLine( $orig_custom_map, $c );
				
				/* Potential conflict, are both modifications to this line the same? */
				if ( count( $test1 ) AND count( $test2 ) AND implode( "\n", $test1 ) != implode( "\n", $test2 ) )
				{
					$conflict++;
					
					$final[] = "<ips:conflict id=\"{$conflict}\">";
					$final[] = "<ips:cblock type=\"original\">";
					
					if ( count( $test2 ) )
					{
						foreach( $test2 as $t )
						{
							$final[] = $t;
						}
					}
					
					$final[] = "</ips:cblock>";
					$final[] = "<ips:cblock type=\"new\">";
					
					if ( count( $test1 ) )
					{
						foreach( $test1 as $t )
						{
							$final[] = $t;
						}
					}
					
					$final[] = "</ips:cblock>";
					$final[] = "</ips:conflict>";
				}
				else
				{
					if ( count( $test1 ) )
					{
						foreach( $test1 as $t )
						{
							$final[] =  $t;
						}
					}
					
					if ( count( $test2 ) )
					{
						foreach( $test2 as $t )
						{
							//$final[] =  $t;
						}
					}
					
					$final[] = $left['data'];
				}
			}
			else if ( $left['type'] == 'del' AND $right['type'] == 'ok' )
			{
				/* Add in anything from left */
				$final = $this->_textToAddAtLine( $orig_new_map, $c, $final, 'lnxx' );
				
				/* One line or more exists in both? */
				$final = $this->_textToAddAtLine( $orig_custom_map, $c, $final, 'lc--' );
			}
			else if ( $left['type'] == 'ok' AND $right['type'] == 'del' )
			{
				/* Add in right */
				$final = $this->_textToAddAtLine( $orig_custom_map, $c, $final, 'rcxx' );
				
				/* Anything to add from the new side? */
				$final = $this->_textToAddAtLine( $orig_new_map, $c, $final, 'rn--' );
			}
			else if ( $left['type'] == 'del' AND $right['type'] == 'del' )
			{
				$test1 = $this->_textToAddAtLine( $orig_new_map, $c );
				$test2 = $this->_textToAddAtLine( $orig_custom_map, $c );
				
				/* Potential conflict, are both modifications to this line the same? */
				if ( count( $test1 ) AND count( $test2 ) AND  implode( "\n", $test1 ) != implode( "\n", $test2 ) )
				{
					$conflict++;
					
					$final[] = "<ips:conflict id=\"{$conflict}\">";
					$final[] = "<ips:cblock type=\"original\">";
					
					if ( count( $test2 ) )
					{
						foreach( $test2 as $t )
						{
							$final[] = $t;
						}
					}
					
					$final[] = "</ips:cblock>";
					$final[] = "<ips:cblock type=\"new\">";
					
					if ( count( $test1 ) )
					{
						foreach( $test1 as $t )
						{
							$final[] = $t;
						}
					}
					
					$final[] = "</ips:cblock>";
					$final[] = "</ips:conflict>";
				}
				else
				{
					if ( count( $test1 ) )
					{
						foreach( $test1 as $t )
						{
							/* We're removing, dumbass */
							//$final[] = $c . ' dexx1' . $t;
						}
					}
						
					if ( count( $test2 ) )
					{
						foreach( $test2 as $t )
						{
							/* We're removing, dumbass */
							//$final[] = $c . ' dexx2 ' . $t;
						}
					}
				}
			}
			
			$c++;
		}
		
		return implode( "\n", $final );
	}
	
	/**
	 * Returns an array slice for the ORIG line number X
	 *
	 * @access	private
	 * @param	array		Re-processed Diff Array
	 * @param	int			ORIG line number
	 * @return	array
	 */
	private function _sliceFromLineNumber( $data, $n )
	{
		foreach ( $data as $i )
		{
			if ( $i['line'] == $n AND $i['type'] != 'ins' )
			{
				return $i;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns an array of lines to add at the current ORIG line number X
	 *
	 * @access	private
	 * @param	array		Re-processed Diff Array
	 * @param	int			ORIG line number
	 * @param	array 		Array to add to
	 * @return	array
	 */
	private function _textToAddAtLine( $data, $n, $return=array(), $p='' )
	{
		$return = ( is_array( $return ) AND count( $return ) ) ? $return : array();
		
		foreach ( $data as $i )
		{
			if ( $i['line'] == $n AND $i['type'] == 'ins' )
			{ 
				$return[] = $i['data'];
			}
		}
		
		return $return;
	}
	
	/**
	 * Re-process DIFF.
	 * Figures out mappings to original line numbers, etc
	 *
	 * @access	private
	 * @param	array 		Array of DIFF data
	 * @return	array
	 */
	private function _fetchProcessed( $data, $log=0 )
	{
		/* Counter to ensure unique entry in $return */
		$c = 0;
		
		/* Counter to keep a corrilation with the original line numbering */
		$n = 0;
		
		/* Variable to retain value of $n when processing insertion rows */
		$old_n = 0;
		
		/* Flag to note if the previous row was a delete and therefore treat a proceeding insert as a replacement */
		$delete_was_last = 0;
		
		/* Freeze the line number when we first see a delete line (assume that more may follow if removing a block */
		$freeze = 0;
		
		foreach( $data as $l )
		{		
			if ( substr( $l, 0, 1 ) == '+' )
			{
				/* AAA: If the last line was a delete, this is treated as a replacement */
				if ( $delete_was_last )
				{
					/* We want to ensure that the insert retains the same ORIGINAL line number as the FIRST line of the recent delete(s)
					  So we effectively 'freeze' N for the inserts and store what would be the next number in $old_n */
					$old_n = ( $old_n ) ? $old_n : $n;
					$n     = $freeze;
				}
				else if ( $freeze )
				{
					/* Ok, the previous was an insert after a delete line, so use the line number that would have been the first delete line number */
					$n = $old_n;
				}
				
				$return[ $c ] = array( 'data' => substr( $l, 1 ),
									   'type' => 'ins',
									   'line' => $n );
				
				/* Delete was not last. Reset freeze flag */  
				$delete_was_last = 0;
				$freeze          = 0;
			}
			else if ( substr( $l, 0, 1 ) == '-' )
			{
				$return[ $c ] = array( 'data' => substr( $l, 1 ),
									   'type' => 'del',
									   'line' => $n );
				
				/* We want to 'freeze' the line number if this is the first delete line */ 
				$freeze = ( $delete_was_last ) ? $freeze : $n;
				
				/* AAA: We've just deleted a line, so flag this up because if an insert is next, then it's a replacement */
				$delete_was_last = 1;
				
				/* BBB: We deleted a line from the original, so increment the original line number */
				$n++;
			}
			else
			{
				/* If this is a line after an insert or delete, use the old_n line number */
				if ( $old_n )
				{
					$n = $old_n;
				}
				
				$return[ $c ] = array( 'data' => $l,
								       'type' => 'ok',
									   'line' => $n );
									   
				$n++;
				$delete_was_last = 0;
				$freeze          = 0;
				$old_n			 = 0;
			}
			
			$c++;
		}
		
		//print_r( $return );
		return $return;
	}
	
	/**
	 * Add debug messages
	 *
	 * @access	private
	 * @param	string		TITLE
	 * @param	mixed		Data
	 */
	private function _addDebugMsg( $title, $data )
	{
		$this->_DEBUG[] = $title . "\n" . var_export( $data, TRUE );
	}

}