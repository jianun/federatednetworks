<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Tagging Functions
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		9th March 2005 11:03
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class tagFunctions
{
	/**#@+
	 * Registry objects
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	/**#@-*/
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object		Registry object
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Make object
		//-----------------------------------------
		
		$this->registry = $registry;
		$this->DB	    = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
	}
	
	/**
	 * Update the hidden status of tags
	 *
	 * @access	public
	 * @param	array 		Where clause information
	 * @param	int			value to update tag_hidden
	 */
	public function toggleTagsHiddenStatus( $where, $hidden=0 )
	{
		if( !is_array($where) OR !count($where) )
		{
			return;
		}
		
		$whereClause		= array();
		
		if( $where['app'] )
		{
			$whereClause[]	= "app='{$where['app']}'";
		}
		
		if( $where['type'] )
		{
			$whereClause[]	= "type='{$where['type']}'";
		}
		
		if( $where['type_id'] )
		{
			$whereClause[]	= "type_id" . ( strtolower($where['type_id_type']) == 'in' ? " IN(" : "=" ) . $where['type_id'] . ( strtolower($where['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $where['type_2'] )
		{
			$whereClause[]	= "type_2='{$where['type_2']}'";
		}
		
		if( $where['type_id_2'] )
		{
			$whereClause[]	= "type_id_2" . ( strtolower($where['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $where['type_id_2'] . ( strtolower($where['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		if( !count($whereClause) )
		{
			return '';
		}
		
		$this->DB->update( 'tags_index', array( 'tag_hidden' => intval( $hidden ) ), implode( ' AND ', $whereClause ) );
	}
	
	/**
	 * Retrieve tags to parse out in the HTML output
	 *
	 * @access	public
	 * @param	string		HTML to search out replacements in
	 * @param	array 		Where clause information
	 * @param	string		URL to prepend to the tag
	 * @param	string		Replacement string to find (use ? and pass "watch_for" in $where clause)
	 * @param	string		SEO Template
	 * @return	string		HTML to use for tags
	 */
	public function parseTags( $html, $where, $url, $replacement, $seoTitle='', $seoTemplate='' )
	{
		if( !is_array($where) OR !count($where) OR !$url )
		{
			return;
		}
		
		$whereClause		= array();
		$output				= array();
		
		if( $where['app'] )
		{
			$whereClause[]	= "app='{$where['app']}'";
		}
		
		if( $where['type'] )
		{
			$whereClause[]	= "type='{$where['type']}'";
		}
		
		if( $where['type_id'] )
		{
			$whereClause[]	= "type_id" . ( strtolower($where['type_id_type']) == 'in' ? " IN(" : "=" ) . $where['type_id'] . ( strtolower($where['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $where['type_2'] )
		{
			$whereClause[]	= "type_2='{$where['type_2']}'";
		}
		
		if( $where['type_id_2'] )
		{
			$whereClause[]	= "type_id_2" . ( strtolower($where['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $where['type_id_2'] . ( strtolower($where['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		if( !count($whereClause) )
		{
			return '';
		}
		
		$this->DB->build( array(
									'select'	=> '*',
									'from'		=> 'tags_index',
									'where'		=> implode( ' AND ', $whereClause ),
							)		);
		$this->DB->execute();
		
		while( $r = $this->DB->fetch() )
		{
			if( $seoTemplate AND $seoTitle )
			{
				$_url = $this->registry->output->formatUrl( $url . urlencode( $r['tag'] ), $seoTitle, $seoTemplate );
			}
			else
			{
				$_url = $url . urlencode( $r['tag'] );
			}
			
			$output[ $r[ $where['watch_for'] ] ][]     = "<a href='{$_url}'>{$r['tag']}</a>";
		}

		foreach( $output as $id => $foundTags )
		{
			$thisTags	= implode( ", ", $foundTags );
			
			$html		= str_replace( str_replace( '?', $id, $replacement), $thisTags, $html );
		}
		
		return $html;
	}
	
	/**
	 * Fetch X most hit tags
	 *
	 * @access	public
	 * @param	array 		Where clause information
	 * @param	integer		Total number of items
	 * @return	string		array of tag names
	 */
	public function getTopXTags( $where, $items=10 )
	{
		if( !is_array($where) OR !count($where) )
		{
			return;
		}
		
		$whereClause		= array();
		$tags				= array();
		
		if( $where['app'] )
		{
			$whereClause[]	= "app='{$where['app']}'";
		}
		
		if( $where['type'] )
		{
			$whereClause[]	= "type='{$where['type']}'";
		}
		
		if( $where['type_id'] )
		{
			$whereClause[]	= "type_id" . ( strtolower($where['type_id_type']) == 'in' ? " IN(" : "=" ) . $where['type_id'] . ( strtolower($where['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $where['type_2'] )
		{
			$whereClause[]	= "type_2='{$where['type_2']}'";
		}
		
		if( $where['type_id_2'] )
		{
			$whereClause[]	= "type_id_2" . ( strtolower($where['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $where['type_id_2'] . ( strtolower($where['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		if( !count($whereClause) )
		{
			return '';
		}
		
		$this->DB->build( array(
									'select'	=> 'COUNT(tag) as times, tag',
									'from'		=> 'tags_index',
									'where'		=> implode( ' AND ', $whereClause ),
									'group'		=> 'tag',
									'limit'     => array( 0, intval( $items ) ),
									'order'		=> 'times DESC',
							)		);
		$this->DB->execute();
		
		while( $r = $this->DB->fetch() )
		{
			$tags[]	= $r['tag'];
		}

		return $tags;
	}
	
	/**
	 * Fetch all entry tags
	 *
	 * @access	public
	 * @param	array 		Where clause information
	 * @return	string		array of tag names
	 */
	public function fetchEntryTags( $where )
	{
		if( !is_array($where) OR !count($where) )
		{
			return;
		}
		
		$whereClause		= array();
		$tags				= array();
		
		if( $where['app'] )
		{
			$whereClause[]	= "app='{$where['app']}'";
		}
		
		if( $where['type'] )
		{
			$whereClause[]	= "type='{$where['type']}'";
		}
		
		if( $where['type_id'] )
		{
			$whereClause[]	= "type_id" . ( strtolower($where['type_id_type']) == 'in' ? " IN(" : "=" ) . $where['type_id'] . ( strtolower($where['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $where['type_2'] )
		{
			$whereClause[]	= "type_2='{$where['type_2']}'";
		}
		
		if( $where['type_id_2'] )
		{
			$whereClause[]	= "type_id_2" . ( strtolower($where['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $where['type_id_2'] . ( strtolower($where['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		if ( isset( $where['get_hidden'] ) AND $where['get_hidden'] != '*' )
		{
			$whereClause[] = "tag_hidden=" . intval( $where['get_hidden'] );
		}
		else
		{
			$whereClause[] = "tag_hidden IN (0,1)";
		}
		
		if( !count($whereClause) )
		{
			return '';
		}
		
		$this->DB->build( array( 'select'	=> '*',
								 'from'		=> 'tags_index',
								 'where'		=> implode( ' AND ', $whereClause ) ) );
		$this->DB->execute();
		
		while( $r = $this->DB->fetch() )
		{
			$tags[]	= $r['tag'];
		}

		return $tags;
	}
	
	/**
	 * Retrieve a tag cloud
	 *
	 * @access	public
	 * @param	array 		Where clause information
	 * @param	integer		Total number of items
	 * @param	string		URL to prepend to the tag
	 * @param	string		SEO Title
	 * @param	string		SEO Template
	 * @return	string		HTML to use for tags
	 */
	public function getTagCloud( $where, $items, $url, $seoTitle='', $seoTemplate='' )
	{
		if( !is_array($where) OR !count($where) OR !$url )
		{
			return;
		}
		
		$whereClause		= array( 0 => 'tag_hidden=0' );
		$output				= array();
		
		if( $where['app'] )
		{
			$whereClause[]	= "app='{$where['app']}'";
		}
		
		if( $where['type'] )
		{
			$whereClause[]	= "type='{$where['type']}'";
		}
		
		if( $where['type_id'] )
		{
			$whereClause[]	= "type_id" . ( strtolower($where['type_id_type']) == 'in' ? " IN(" : "=" ) . $where['type_id'] . ( strtolower($where['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $where['type_2'] )
		{
			$whereClause[]	= "type_2='{$where['type_2']}'";
		}
		
		if( $where['type_id_2'] )
		{
			$whereClause[]	= "type_id_2" . ( strtolower($where['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $where['type_id_2'] . ( strtolower($where['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		if( !count($whereClause) )
		{
			return '';
		}
		
		$this->DB->build( array(
									'select'	=> 'COUNT(tag) as times, tag',
									'from'		=> 'tags_index',
									'where'		=> implode( ' AND ', $whereClause ),
									'group'		=> 'tag',
									'order'		=> 'tag ASC',
							)		);
		$this->DB->execute();
		
		while( $r = $this->DB->fetch() )
		{
			$size		= round( $r['times'], $items );
			$size		= $size < 1 ? 1 : $size;
			$size		= $size > 6 ? 6 : $size;
			
			if( $seoTitle && $seoTemplate )
			{
				$_url = $this->registry->output->formatUrl( "{$url}{$r['tag']}", $seoTitle, $seoTemplate );
			}
			else
			{
				$_url = "{$url}{$r['tag']}";
			}
			
			$output[]	= "<li class='level{$size}'><a href='{$_url}' rel='tag'>{$r['tag']}</a></li>";
		}

		return "<ul class='tagList'>" . implode( "\n", $output ) . "</ul>";
	}
	
	/**
	 * Move tags
	 * Moves tags
	 *
	 * @access	public
	 * @param	array		Where clause array
	 * @param	array		To clause array
	 */
	public function moveTags( $where, $to )
	{
		if ( ! is_array( $where ) OR ! count($where) OR ! is_array( $to ) OR ! count( $to ) )
		{
			return false;
		}
		
		$whereClause = array();
		$toClause    = array();
		
		/* From... */
		if ( $where['app'] )
		{
			$whereClause[]	= "app='{$where['app']}'";
		}
		
		if( $where['type'] )
		{
			$whereClause[]	= "type='{$where['type']}'";
		}
		
		if( $where['type_id'] )
		{
			$whereClause[]	= "type_id" . ( strtolower($where['type_id_type']) == 'in' ? " IN(" : "=" ) . $where['type_id'] . ( strtolower($where['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $where['type_2'] )
		{
			$whereClause[]	= "type_2='{$where['type_2']}'";
		}
		
		if( $where['type_id_2'] )
		{
			$whereClause[]	= "type_id_2" . ( strtolower($where['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $where['type_id_2'] . ( strtolower($where['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		/* To... */
		if ( $to['app'] )
		{
			$toClause[]	= "app='{$to['app']}'";
		}
		
		if( $to['type'] )
		{
			$toClause[]	= "type='{$to['type']}'";
		}
		
		if( $to['type_id'] )
		{
			$toClause[]	= "type_id" . ( strtolower($to['type_id_type']) == 'in' ? " IN(" : "=" ) . $to['type_id'] . ( strtolower($to['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $to['type_2'] )
		{
			$toClause[]	= "type_2='{$to['type_2']}'";
		}
		
		if( $to['type_id_2'] )
		{
			$toClause[]	= "type_id_2" . ( strtolower($to['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $to['type_id_2'] . ( strtolower($to['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		if ( ! count($toClause) OR ! count( $whereClause ) )
		{
			return false;
		}
		
		/* Move THEM */
		$this->DB->update( 'tags_index', implode( ', ', $toClause ), implode( ' AND ', $whereClause ), false, true );
	}
	
	/**
	 * Move tags
	 * Moves tags
	 *
	 * @access	public
	 * @param	array		Where clause array
	 * @param	array		To clause array
	 */
	public function deleteTags( $where )
	{
		if ( ! is_array( $where ) OR ! count($where) )
		{
			return false;
		}
		
		$whereClause = array();
		
		/* From... */
		if ( $where['app'] )
		{
			$whereClause[]	= "app='{$where['app']}'";
		}
		
		if( $where['type'] )
		{
			$whereClause[]	= "type='{$where['type']}'";
		}
		
		if( $where['type_id'] )
		{
			$whereClause[]	= "type_id" . ( strtolower($where['type_id_type']) == 'in' ? " IN(" : "=" ) . $where['type_id'] . ( strtolower($where['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $where['type_2'] )
		{
			$whereClause[]	= "type_2='{$where['type_2']}'";
		}
		
		if( $where['type_id_2'] )
		{
			$whereClause[]	= "type_id_2" . ( strtolower($where['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $where['type_id_2'] . ( strtolower($where['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		if ( ! count( $whereClause ) )
		{
			return false;
		}
		
		/* Move THEM */
		$this->DB->delete( 'tags_index', implode( ' AND ', $whereClause ) );
	}
	
	/**
	 * Store tags
	 * Takes the tags and stores them in the database
	 *
	 * @access	public
	 * @param	string		Comma-separated tags list
	 * @param	array		Where clause array
	 * @param	integer		[Optional] Member id
	 * @return	integer		Number of tags stored
	 */
	public function storeTags( $tags, $where, $member_id=0 )
	{
		if( !is_array($where) OR !count($where) OR !$tags )
		{
			return 0;
		}
		
		$whereClause		= array();
		$tags				= explode( ',', $tags );
		$count				= 0;
		
		if( $where['app'] )
		{
			$whereClause[]	= "app='{$where['app']}'";
		}
		
		if( $where['type'] )
		{
			$whereClause[]	= "type='{$where['type']}'";
		}
		
		if( $where['type_id'] )
		{
			$whereClause[]	= "type_id" . ( strtolower($where['type_id_type']) == 'in' ? " IN(" : "=" ) . $where['type_id'] . ( strtolower($where['type_id_type']) == 'in' ? ")" : "" );
		}
		
		if( $where['type_2'] )
		{
			$whereClause[]	= "type_2='{$where['type_2']}'";
		}
		
		if( $where['type_id_2'] )
		{
			$whereClause[]	= "type_id_2" . ( strtolower($where['type_id_2_type']) == 'in' ? " IN(" : "=" ) . $where['type_id_2'] . ( strtolower($where['type_id_2_type']) == 'in' ? ")" : "" );
		}
		
		if( !count($whereClause) )
		{
			return 0;
		}

		$this->DB->delete( 'tags_index', implode( ' AND ', $whereClause ) );
		
		foreach( $tags as $tag )
		{
			$tag = trim( $tag );
			
			if( !$tag )
			{
				continue;
			}
			
			$insert	= array(
							'app'		 => $where['app'],
							'tag'		 => $tag,
							'updated'	 => time(),
							'member_id'	 => $member_id ? $member_id : $this->memberData['member_id'],
							'type'		 => $where['type'],
							'type_id'	 => $where['type_id'],
							'type_2'	 => $where['type_2'],
							'type_id_2'	 => $where['type_id_2'],
							'misc'		 => '',
							'tag_hidden' => intval( $where['tag_hidden'] )
							);
			
			$this->DB->insert( 'tags_index', $insert );
			
			$count++;
		}
		
		return $count;
	}
}