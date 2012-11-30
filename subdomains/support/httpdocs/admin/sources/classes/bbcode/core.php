<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * BBCode parsing core - common methods
 * Last Updated: $Date: 2010-07-13 19:56:23 -0400 (Tue, 13 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		9th March 2005 11:03
 * @version		$Revision: 6644 $
 *
 * Basic usage examples
 * <code>
 * $parser           =  new parseBbcode( $registry );
 * 
 * # If you wish convert posted text to store in the database
 * $parser->parse_smilies = 1;
 * $parser->parse_bbcode  = 1;
 * $parser->parsing_section = 'your section key';
 * $bbcode_text = $parser->preDbParse( $_POST['text'] );
 * 
 * # If you wish to display this content
 * $parser->parse_html    = 0;
 * $parser->parse_nl2br   = 1;
 * $parser->parsing_section = 'your section key';
 * $parser->parsing_mgroup = 'member group id of poster';
 * $parser->parsing_mgroup_others = 'member other group ids of poster';
 * $ready_to_print        = $parser->preDisplayParse(  $bbcode_text  );
 * 
 * # If you wish to convert already converted BBCode back into the raw format
 * # (for use in an editing screen, for example) use this:
 * $raw_post = $parser->preEditParse( $parsed_text );
 * 
 * # Of course, if you're using the rich text editor (WYSIWYG) then you don't want to uncovert the HTML
 * # otherwise the rich text editor will show unparsed BBCode tags, and not formatted HTML. In this case use this:
 * $raw_post = $parser->convert_ipb_html_to_html( $parsed_text );
 * </code>
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_bbcode_core
{
	/**
	 * Parse emoticons?
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $parse_smilies			= true;

	/**
	 * Parse HTML?
	 * HIGHLY NOT RECOMMENDED IN MOST CASES
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $parse_html				= false;

	/**
	 * Parse bbcode?
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $parse_bbcode			= true;

	/**
	 * Strip quotes?
	 * Strips quotes from the resulting text
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $strip_quotes			= false;
	
	/**
	 * Wordwrap cutoff
	 * Attempts to cut strings as close as possible at these points
	 *
	 * @access	public
	 * @var		integer
	 */	
	public $parse_wordwrap			= 0;

	/**
	 * Auto convert newlines to html line breaks
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $parse_nl2br				= true;

	/**
	 * Bypass badwords?
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $bypass_badwords			= false;

	/**
	 * Section keyword for parsing area
	 *
	 * @access	public
	 * @var		string
	 */	
	public $parsing_section			= 'post';
	
	/**
	 * Group id of poster
	 *
	 * @access	public
	 * @var		int
	 */	
	public $parsing_mgroup			= 0;
	
	/**
	 * Value of mgroup_others for poster
	 *
	 * @access	public
	 * @var		string
	 */	
	public $parsing_mgroup_others	= '';
	
	/**
	 * Allow unicode (parses escaped entities to actual entities)
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $allow_unicode			= false;

	/**
	 * Maximum number of embeded quotes
	 *
	 * @access	public
	 * @var		integer
	 */	
	public $max_embed_quotes		= 15;
	
	/**
	 * Error code stored
	 *
	 * @access	public
	 * @var		string
	 */	
	public $error					= '';
	
	/**
	 * Warning code stored (warning is like an error but will not stop parsing execution)
	 *
	 * @access	public
	 * @var		string
	 */	
	public $warning					= '';
	
	/**
	 * Number of images we've parsed so far
	 *
	 * @access	protected
	 * @var		integer
	 */	
	protected $image_count			= 0;

	/**
	 * Number of emoticons we've parsed so far
	 *
	 * @access	protected
	 * @var		integer
	 */	
	protected $emoticon_count		= 0;
	
	/**
	 * Array of emoticon alt tags
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $emoticon_alts		= array();

	/**
	 * Registry object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $registry;
	
	/**
	 * Database object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $DB;
	
	/**
	 * Settings object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $settings;
	
	/**
	 * Request object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $request;
	
	/**
	 * Language object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $lang;
	
	/**
	 * Member object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $member;
	
	/**
	 * Cache object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $cache;
	
	/**
	 * BBCodes we should parse.
	 * Takes into account what section we are in, and our group.
	 *
	 * @access	protected
	 * @var		array
	 */	
	protected $_bbcodes				= array();
	
	/**
	 * Plugin objects
	 *
	 * @access	private
	 * @var		array 					Holds plugin objects
	 */	
	private $plugins					= array();
	
	/**
	 * Current position in the text document
	 *
	 * @access	private
	 * @var		integer
	 */	
	private $cur_pos					= 0;
	
	/**
	 * Multi-dimensional array of bbcodes not being parsed inside
	 *
	 * @access	protected
	 * @var		array
	 */	
	protected $noParseStorage				= array();
	
	/**
	 * Identifier for replacement
	 *
	 * @access	protected
	 * @var		integer
	 */
	protected $_storedNoParsing			= 0;
	
	/**
	 * Emoticon code
	 *
	 * @access	private
	 * @var		string
	 */	
	private $_emoCode					= '';
	
	/**
	 * Emoticon image
	 *
	 * @access	private
	 * @var		string
	 */	
	private $_emoImage					= '';
		
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object		Registry object
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry )
	{
		/* Make object */
		$this->registry = $registry;
		$this->DB	   = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang	 = $this->registry->getClass('class_localization');
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
		
		//require_once( IPS_ROOT_PATH . 'sources/interfaces/interface_bbcode.php' );

		$_NOW = IPSDebug::getMemoryDebugFlag();
		
		$this->initOurBbcodes();
		
		IPSDebug::setMemoryDebugFlag( "BBCodes initialized", $_NOW );
		
		/* Check for emoticons */
		if ( ! is_array( $this->caches['emoticons'] ) OR ! count( $this->caches['emoticons'] ) )
		{
			$this->cache->rebuildCache( 'emoticons', 'global' );
		}
	}
	
	/**
	 * Initialize our bbcodes
	 *
	 * @access	public
	 * @return	void
	 */
	public function initOurBbcodes()
	{
		$this->_bbcodes		= array();

		foreach( $this->cache->getCache('bbcode') as $bbcode )
		{
			/* Cheat a bit */
			if ( $bbcode['bbcode_tag'] == 'code' OR $bbcode['bbcode_tag'] == 'acronym' )
			{
				$bbcode['bbcode_no_auto_url_parse'] = 1;
			}
			
			//-----------------------------------------
			// BBcode allowed in this section?
			//-----------------------------------------

			if( $bbcode['bbcode_sections'] != 'all' )
			{
				$pass		= false;
				$sections	= explode( ',', $bbcode['bbcode_sections'] );
				
				foreach( $sections as $section )
				{
					if( $section == $this->parsing_section OR $this->parsing_section == 'global' )
					{
						$pass = true;
						break;
					}
				}
				
				if( !$pass )
				{
					continue;
				}
			}
			
			//-----------------------------------------
			// Store into the array
			//-----------------------------------------
			
			$this->_bbcodes[ $bbcode['bbcode_parse'] == 1 ? 'db' : 'display' ][ $bbcode['bbcode_tag'] ]	= $bbcode;
		}
	}

	/**
	 * Reset bbcode internal pointers
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _resetPointers()
	{
		$this->error			= '';
		$this->image_count		= 0;
		$this->emoticon_count	= 0;
	}

	/**
	 * Remove session keys from URLs
	 *
	 * @access	protected
	 * @param	array		Array of matches
	 * @return	string		Converted text
	 */
	protected function _bashSession( $matches=array() )
	{
		$start_tok	= str_replace( '&amp;', '&', $matches[1] );
		$end_tok	= str_replace( '&amp;', '&', $matches[3] );

		if ( ( $start_tok == '?' OR $start_tok == '&' ) and $end_tok == '')
		{
			return '';
		}
		else if ( $start_tok == '?' and $end_tok == '&' )
		{
			return '?';
		}
		else if ( $start_tok == '&' and $end_tok == '&' )
		{
			return "&";
		}
		else
		{
			return $start_tok . $end_tok;
		}
	}

	/**
	 * Check against XSS
	 *
	 * @access	public
	 * @param	string		Original string
	 * @param	boolean		Fix script HTML tags
	 * @return	string		"Cleaned" text
	 */
	public function checkXss( $txt='', $fixScript=false, $tag='' )
	{
		//-----------------------------------------
		// Opening script tags...
		// Check for spaces and new lines...
		//-----------------------------------------
		
		if ( $fixScript )
		{
			$txt = preg_replace( "#<(\s+?)?s(\s+?)?c(\s+?)?r(\s+?)?i(\s+?)?p(\s+?)?t#is"        , "&lt;script" , $txt );
			$txt = preg_replace( "#<(\s+?)?/(\s+?)?s(\s+?)?c(\s+?)?r(\s+?)?i(\s+?)?p(\s+?)?t#is", "&lt;/script", $txt );
		}
		
		/* got a tag? */
		if ( $tag )
		{
			$tag = strip_tags( $tag, '<br>' );
			
			switch ($tag)
			{
				case 'entry':
				case 'blog':
				case 'topic':
				case 'post':
					$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
					if ( ! is_numeric( $test ) )
					{
						$txt	= false;
					}
				break;
				
				case 'acronym':
					$test  = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
					$test1 = str_replace( array( '<', ">", '[', ']' ), "", $test );//IPSText::alphanumericalClean( $test, '.+&#; ' );
					if ( $test != $test1 )
					{
						$txt	= false;
					}
				break;
				
				case 'email':
					$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
					$test = ( IPSText::checkEmailAddress( $test ) ) ? $txt : FALSE;
				break;
				
				case 'font':
				case 'color':
				case 'background':
					/* Make sure it's clean */
					$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
					$test1 = IPSText::alphanumericalClean( $test, '#.+, ' );
					if ( $test != $test1 )
					{
						$txt	= false;
					}
				break;
				
				default:
					$_regex	= null;
					
					foreach( $this->cache->getCache('bbcode') as $bbcode )
					{
						if( $bbcode['bbcode_tag'] == $tag )
						{
							$_regex	= $bbcode['bbcode_custom_regex'];
							break;
						}
					}
					
					if( $_regex )
					{
						$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );

						if( !preg_match( $_regex, $test ) )
						{
							$txt	= false;
						}
					}
				break;
			}
			

			/* If we didn't actually get any option data, then return false */
			$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
			
			if ( strlen($txt) AND strlen( $test ) < 1 )
			{
				$txt = false;
			}

			if ( $txt === false )
			{
				return false;
			}
			
			/* Still here? Safety, then */
			$txt	= strip_tags( $txt, '<br>' );
			
			if( strpos( $txt, '[' ) !== false OR strpos( $txt, ']' ) !== false )
			{
				$txt	= str_replace( array( '[', ']' ), array( '&#91;', '&#93;' ), $txt );
			}
		}

		//-----------------------------------------
		// Here we can do some generic checking for XSS
		// This should not be considered fool proof, though can provide
		//	a centralized point for maintenance and checking
		//-----------------------------------------
		
		$txt = preg_replace( "/(j)avascript/i" , "\\1&#097;v&#097;script", $txt );
		//$txt = str_ireplace( "alert"      , "&#097;lert"          , $txt );
		//$txt = preg_replace( "/(b)(e)(h)(a)(v)(i)(o)(r)/is"   , "\\1\\2\\3<b></b>\\4\\5\\6\\7\\8"    	  , $txt );
		$txt = preg_replace( "/(e)((\/\*.*?\*\/)*)x((\/\*.*?\*\/)*)p((\/\*.*?\*\/)*)r((\/\*.*?\*\/)*)e((\/\*.*?\*\/)*)s((\/\*.*?\*\/)*)s((\/\*.*?\*\/)*)i((\/\*.*?\*\/)*)o((\/\*.*?\*\/)*)n/is" , "\\1xp<b></b>ressi&#111;n"     , $txt );
		$txt = preg_replace( "/(e)((\\\|&#092;)*)x((\\\|&#092;)*)p((\\\|&#092;)*)r((\\\|&#092;)*)e((\\\|&#092;)*)s((\\\|&#092;)*)s((\\\|&#092;)*)i((\\\|&#092;)*)o((\\\|&#092;)*)n/is" 	  , "\\1xp<b></b>ressi&#111;n"     	  , $txt );
		$txt = preg_replace( "/m((\\\|&#092;)*)o((\\\|&#092;)*)z((\\\|&#092;)*)\-((\\\|&#092;)*)b((\\\|&#092;)*)i((\\\|&#092;)*)n((\\\|&#092;)*)d((\\\|&#092;)*)i((\\\|&#092;)*)n((\\\|&#092;)*)g/is" 	  , "moz-<b></b>b&#105;nding"     	  , $txt );
		$txt = str_ireplace( "about:"     , "&#097;bout:"         , $txt );
		$txt = str_ireplace( "<body"      , "&lt;body"            , $txt );
		$txt = str_ireplace( "<html"      , "&lt;html"            , $txt );
		$txt = str_ireplace( "document." , "&#100;ocument."      , $txt );
		$txt = str_ireplace( "window."   , "wind&#111;w."      , $txt );
		
		$event_handlers	= array( 'mouseover', 'mouseout', 'mouseup', 'mousemove', 'mousedown', 'mouseenter', 'mouseleave', 'mousewheel',
								 'contextmenu', 'click', 'dblclick', 'load', 'unload', 'submit', 'blur', 'focus', 'resize', 'scroll',
								 'change', 'reset', 'select', 'selectionchange', 'selectstart', 'start', 'stop', 'keydown', 'keyup',
								 'keypress', 'abort', 'error', 'dragdrop', 'move', 'moveend', 'movestart', 'activate', 'afterprint',
								 'afterupdate', 'beforeactivate', 'beforecopy', 'beforecut', 'beforedeactivate', 'beforeeditfocus',
								 'beforepaste', 'beforeprint', 'beforeunload', 'begin', 'bounce', 'cellchange', 'controlselect',
								 'copy', 'cut', 'paste', 'dataavailable', 'datasetchanged', 'datasetcomplete', 'deactivate', 'drag',
								 'dragend', 'dragleave', 'dragenter', 'dragover', 'drop', 'end', 'errorupdate', 'filterchange', 'finish',
								 'focusin', 'focusout', 'help', 'layoutcomplete', 'losecapture', 'mediacomplete', 'mediaerror', 'outofsync',
								 'pause', 'propertychange', 'progress', 'readystatechange', 'repeat', 'resizeend', 'resizestart', 'resume',
								 'reverse', 'rowsenter', 'rowexit', 'rowdelete', 'rowinserted', 'seek', 'syncrestored', 'timeerror',
								 'trackchange', 'urlflip',
								);
		
		foreach( $event_handlers as $handler )
		{
			$txt = str_ireplace( 'on' . $handler, '&#111;n' . $handler, $txt );
		}

		return $txt;
	}

	/**
	 * Replace bad words
	 *
	 * @access	public
	 * @param	string	Raw text
	 * @return	string	Converted text
	 */
	public function badWords( $text = "" )
	{
		if ($text == "")
		{
			return "";
		}
		
		if ( $this->bypass_badwords == 1 )
		{
			return $text;
		}
		
		$temp_text = $text;
		
		//-----------------------------------------
		// Convert back entities
		//-----------------------------------------
			
		for( $i = 65; $i <= 90; $i++ )
		{
			$text = str_replace( "&#" . $i . ";", chr($i), $text );
		}
		
		for( $i = 97; $i <= 122; $i++ )
		{
			$text = str_replace( "&#" . $i . ";", chr($i), $text );
		}		
		
		//-----------------------------------------
		// Go all loopy
		//-----------------------------------------
		
		if ( is_array( $this->cache->getCache('badwords') ) )
		{
			if ( count($this->cache->getCache('badwords')) > 0 )
			{
				foreach( $this->cache->getCache('badwords') as $r )
				{
					$replace	= $r['swop'] ? $r['swop'] : '######';
					
					if ( $r['m_exact'] )
					{
						$r['type']	= preg_quote( $r['type'], "/" );
						$text		= preg_replace( "/(^|\b|\s)" . $r['type'] . "(\b|!|\?|\.|,|$)/i", "\\1{$replace}\\2", $text );
					}
					else
					{
						//----------------------------
						// 'ass' in 'class' kills css
						//----------------------------
						
						if( $r['type'] == 'ass' )
						{
							$text		= preg_replace( "/(?<!cl)" . $r['type'] . "/i", $replace, $text );
						}
						else
						{
							$text		= str_ireplace( $r['type'], $replace, $text );
						}
					}
				}
			}
		}
		
		return $text ? $text : $temp_text;
	}
	
	/**
	 * Check against blacklisted URLs
	 *
	 * @access	public
	 * @param 	string			Raw posted text
	 * @return	bool			False if blacklisted url present, otherwise true
	 */
	public function checkBlacklistUrls( $t )
	{
		if( !$t )
		{
			return true;
		}

		if ( $this->settings['ipb_use_url_filter'] )
		{
			$list_type = $this->settings['ipb_url_filter_option'] == "black" ? "blacklist" : "whitelist";
			
			if( $this->settings['ipb_url_' . $list_type ] )
			{
				$list_values 	= array();
				$list_values 	= explode( "\n", str_replace( "\r", "", $this->settings['ipb_url_' . $list_type ] ) );
				
				if( $list_type == 'whitelist' )
				{
					$list_values[]	= "http://{$_SERVER['HTTP_HOST']}/*";
				}
				
				if ( count( $list_values ) )
				{
					$good_url = 0;
					
					foreach( $list_values as $my_url )
					{
						if( !trim($my_url) )
						{
							continue;
						}

						$my_url = preg_quote( $my_url, '/' );
						$my_url = str_replace( "\*", "(.*?)", $my_url );
						
						if ( $list_type == "blacklist" )
						{
							if( preg_match( '/' . $my_url . '/i', $t ) )
							{
								return false;
							}
						}
						else
						{
							if ( preg_match( '/' . $my_url . '/i', $t ) )
							{
								$good_url = 1;
							}
						}
					}
					
					if ( ! $good_url AND $list_type == "whitelist" )
					{
						return false;
					}						
				}
			}
		}
		
		return true;
	}

	/**
	 * Custom word wrap : attempts to not break HTML tags (*ha!)
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public function applyWordwrap( $txt="", $chrs=0, $replace="\n" )
	{
		//-----------------------------------------
		// Got chars and limit
		//-----------------------------------------

		if ( $txt == "" )
		{
			return $txt;
		}
		
		if ( $chrs < 1 )
		{
			return $txt;
		}

		//-----------------------------------------
		// Array of tags we won't apply breaking in
		//-----------------------------------------
				
		$noBreak		= array( 'textarea' );
		
		//-----------------------------------------
		// Characters we will break at
		//-----------------------------------------
		
		$breakAt		= array( '?', '/', '.', ',', '!', '%', ';', '-', "'", '"', ':', '>' );
		
		//-----------------------------------------
		// Characters that will reset the wordwrap
		//-----------------------------------------
		
		$resetters		= array( ' ', "\n", "\t" );
		
		//-----------------------------------------
		// Other init...
		//-----------------------------------------
		
		$inTag			= false;
		$totalLength	= strlen( $txt );
		$curPos			= 0;
		$charsSince		= 0;
		$iterations		= 0;
		//-----------------------------------------
		// Loop over each char
		//-----------------------------------------
		
		while( $curPos < $totalLength )
		{
			$curPos++;
			
			/**
			 * @link	http://community.invisionpower.com/tracker/issue-23818-wordwrap-bug-with-amp%3Blt%3B/
			 */
			if( $curPos < 1 )
			{
				break;
			}

			//-----------------------------------------
			// We within a tag?
			//-----------------------------------------

			if( isset( $txt{$curPos} ) && $txt{$curPos} == '<' )
			{
				$inTag	= true;
				$charsSince++;
				continue;
			}
			
			//-----------------------------------------
			// We out of a tag?
			//-----------------------------------------
			
			if( isset( $txt{$curPos} ) && $txt{$curPos} == '>' )
			{
				$inTag	= false;
			}
			
			//-----------------------------------------
			// Within a tag...
			//-----------------------------------------
			
			if( $inTag )
			{
				//-----------------------------------------
				// This a tag we're skipping?
				//-----------------------------------------

				if( in_array( substr( $txt, $curPos, (strpos( $txt, ' ', $curPos ) - $curPos) ), $noBreak ) )
				{
					$curTag		= substr( $txt, $curPos, (strpos( $txt, ' ', $curPos ) - $curPos) );
					$charsSince	= 0;
					$curPos 	= stripos( $txt, '</' . $curTag . '>', $curPos );
					
					continue;
				}
				else if( in_array( substr( $txt, $curPos, (strpos( $txt, '>', $curPos ) - $curPos) ), $noBreak ) )
				{
					$curTag		= substr( $txt, $curPos, (strpos( $txt, '>', $curPos ) - $curPos) );
					$charsSince	= 0;
					$curPos 	= stripos( $txt, '</' . $curTag . '>', $curPos );

					continue;
				}
				
				//-----------------------------------------
				// No, skip tag, but increment chars by 1
				//-----------------------------------------
				
				else
				{
					$charsSince++;
					$curPos 	= strpos( $txt, '>', $curPos ) - 1;
					continue;
				}
			}
			
			//-----------------------------------------
			// Reset the chars since if necessary
			//-----------------------------------------

			if( isset( $txt{$curPos} ) && in_array( $txt{$curPos}, $resetters ) )
			{
				$charsSince	= 0;
				continue;
			}

			//-----------------------------------------
			// Time to break?
			//-----------------------------------------
			
			if( $charsSince >= $chrs )
			{
				if( in_array( $txt{$curPos}, $breakAt ) )
				{
					$txt		= substr_replace( $txt, $replace, $curPos + 1, 0 );
					$curPos		= $curPos + 1 + strlen($replace);
					$charsSince	= 0;
					continue;
				}
			}
			
			//-----------------------------------------
			// One more char since last break
			//-----------------------------------------
			
			$charsSince++;
		}

		return $txt;
	}

	/**
	 * Makes data for quote strings "safe"
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public function makeQuoteSafe( $txt='' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
	
		$begin	= '';
		$end	= '';
		
		//-----------------------------------------
		// Come via preg_replace_callback?
		//-----------------------------------------
		
		if ( is_array( $txt ) )
		{
			$begin = $txt[1];
			$end   = $txt[3];
			$txt   = $txt[2];
		}
		
		//-----------------------------------------
		// Sort name
		//-----------------------------------------
		
		$txt = str_replace( "+", "&#043;" , $txt );
		$txt = str_replace( "-", "&#045;" , $txt );
		$txt = str_replace( ":", "&#58;"  , $txt );
		$txt = str_replace( "[", "&#91;"  , $txt );
		$txt = str_replace( "]", "&#93;"  , $txt );
		$txt = str_replace( ")", "&#41;"  , $txt );
		$txt = str_replace( "(", "&#40;"  , $txt );
		$txt = str_replace( "'", "&#039;" , $txt );
		
		return $begin . $txt . $end;
	}

	/**
	 * Removes bbcode tag + contents within the tag
	 *
	 * @access	public
	 * @param	string		Tag to strip
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public function stripBbcode( $tag, $txt )
	{
		//-----------------------------------------
		// Protect against endless loops
		//-----------------------------------------
		
		static $iteration	= array();
		
		if( array_key_exists( $tag, $iteration ) AND $iteration[ $tag ] > $this->settings['max_bbcodes_per_post'] )
		{
			return $txt;
		}
		
		$iteration[ $tag ]++;
		
		// Got Quotes (tm)? or any tag really
		if( stripos( $txt, '[' . $tag ) !== false )
		{
			//-----------------------------------------
			// First grab start and end positions
			//-----------------------------------------
			
			$start_position = stripos( $txt, '[' . $tag );
			$end_position	= stripos( $txt, '[/' . $tag . ']', $start_position );

			//-----------------------------------------
			// If no end position or start position,
			// we have a mismatched bbcode...return
			//-----------------------------------------
			
			if( $start_position === false OR $end_position === false )
			{
				return $txt;
			}

			//-----------------------------------------
			// Then extract the content inside the bbcode
			//-----------------------------------------
			
			$inner_content	= substr( $txt, stripos( $txt, ']', $start_position ) + 1, $end_position - (stripos( $txt, ']', $start_position ) + 1) );

			//-----------------------------------------
			// Is this bbcode nested in the inner content
			//-----------------------------------------
			
			$extra_closers	= substr_count( $inner_content, '[' . $tag );

			//-----------------------------------------
			// If so we need to move to the last ending tag
			//-----------------------------------------
			
			if( $extra_closers > 0 )
			{
				for( $done=0; $done < $extra_closers; $done++ )
				{
					$end_position = stripos( $txt, '[/' . $tag . ']', $end_position + 1 );
				}
			}

			//-----------------------------------------
			// Get rid of the bbcode opening + content + closing
			//-----------------------------------------
			
			$txt = substr_replace( $txt, '', $start_position, $end_position - $start_position + strlen('[/' . $tag . ']') );

			//-----------------------------------------
			// And parse recursively
			//-----------------------------------------

			return $this->stripBbcode( $tag, trim($txt) );
		}
		else
		{
			return $txt;
		}
	}
	
	
	/**
	 * Remove ALL tags
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @param 	boolean		Whether or not to run through pre-edit-parse first
	 * @param 	boolean		Check "strip from search" option
	 * @return	string		Converted text
	 * @todo 	[Future] Can this (and should this) be done without regex?
	 */
	public function stripAllTags( $txt, $pre_edit_parse=true, $only_search=true )
	{
		if( $pre_edit_parse )
		{
			$txt = $this->preEditParse( $txt );
		}
		
		$txt = $this->stripBbcode( 'quote', $txt );
		
		foreach( $this->cache->getCache('bbcode') as $bbcode )
		{
			if( $only_search && isset( $bbcode['bbcode_strip_search'] ) && $bbcode['bbcode_strip_search'] )
			{
				$txt = $this->stripBbcode( $bbcode['bbcode_tag'], $txt );
			}
			else
			{
				//$txt = preg_replace( "#\[{$bbcode['bbcode_tag']}\](.+?)\[/{$bbcode['bbcode_tag']}\]#is", "\\1 ", $txt );
				$txt = preg_replace( "#\[{$bbcode['bbcode_tag']}=([^\]]+?)\](.+?)\[/{$bbcode['bbcode_tag']}\]#is", "\\2 ", $txt );
				$txt = str_ireplace( "[{$bbcode['bbcode_tag']}]", '', $txt );
				$txt = str_ireplace( "[/{$bbcode['bbcode_tag']}]", '', $txt );
			}
			
			//-----------------------------------------
			// Strip single bbcodes properly
			//-----------------------------------------
			
			if( $bbcode['bbcode_single_tag'] )
			{
				$regex	= $bbcode['bbcode_single_tag'];
				
				//-----------------------------------------
				// If this has option, adjust regex
				//-----------------------------------------
				
				if( $bbcode['bbcode_useoption'] )
				{
					$regex .= '=([^\]]+?)';
				}

				$txt	= preg_replace( "#\[{$regex}\]#is", " ", $txt );
			}
		}
		
		//$txt = preg_replace( "#\[(.+?)\]#is", " ", $txt );
		$txt = preg_replace( "#\[([^\]]+?)=([^\]]+?)\]#is", " ", $txt );
		$txt = preg_replace( "#\[/([^\]]+?)\]#is", " ", $txt );
		$txt = preg_replace( "#\[attachment=(.+?)\]#is", " ", $txt );
		$txt = str_replace( '[*]', '', $txt );

		return $txt;
	}

	/**
	 * Convert special IPB HTML to normal HTML
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public function convertForRTE( $t="" )
	{
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_isForRte', true );
		
		//-----------------------------------------
		// IE handles RTE BR tags differently
		//-----------------------------------------

		$t = str_ireplace( "<BR>",	"<br />",	$t );
		
		//-----------------------------------------
		// Get out codeboxes first...
		//-----------------------------------------
		
		$_codeboxes	= array();
		$_increment	= 0;
		$_codes		= array();
		
		foreach( $this->_bbcodes['display'] as $code => $data )
		{
			if( $data['bbcode_no_parsing'] )
			{
				if( $code == 'img' )
				{
					continue;
				}

				$_codes[]	= $code;
			}
		}
		
		foreach( $_codes as $_aCode )
		{
			while( preg_match( "/\[{$_aCode}\](.+?)\[\/{$_aCode}\]/is", $t, $matches ) )
			{
				$_codeboxes[ $_increment ]	= $matches[0];
				
				$t = str_replace( $matches[0], "__CODEBOX_{$_increment}__", $t );
				
				$_increment++;
			}
		}

		//-----------------------------------------
		// Prevent from parsing inside code
		//-----------------------------------------

		$t = $this->_storeNonParsed( $t, 'display' );

		//-----------------------------------------
		// Now we'll "fix" the replacement html...
		//-----------------------------------------
		
		$_tags = array( 'url', 'left', 'right', 'center', 'indent', 'b', 'i', 'u', 'strike', 'sub', 'sup', 'url', 'img', 'background', 'color', 'size', 'font', 'list' );
		
		foreach( $this->_bbcodes['display'] as $_tag => $_bbcode )
		{
			if( !in_array( $_tag, $_tags ) )
			{
				unset( $this->_bbcodes['display'][ $_tag ] );
			}
		}

		//-----------------------------------------
		// Parse bbcodes for RTE
		//-----------------------------------------
		
		$this->_bbcodes['display']['left']['bbcode_replace']		= '<div align="left">{content}</div>';
		$this->_bbcodes['display']['right']['bbcode_replace']		= '<div align="right">{content}</div>';
		$this->_bbcodes['display']['center']['bbcode_replace']		= '<div align="center">{content}</div>';
		$this->_bbcodes['display']['indent']['bbcode_replace']		= '<blockquote>{content}</blockquote>';
		$this->_bbcodes['display']['b']['bbcode_replace']			= '<b>{content}</b>';
		$this->_bbcodes['display']['i']['bbcode_replace']			= '<i>{content}</i>';
		$this->_bbcodes['display']['u']['bbcode_replace']			= '<u>{content}</u>';
		$this->_bbcodes['display']['strike']['bbcode_replace']		= '<strike>{content}</strike>';
		$this->_bbcodes['display']['sub']['bbcode_replace']			= '<sub>{content}</sub>';
		$this->_bbcodes['display']['sup']['bbcode_replace']			= '<sup>{content}</sup>';
		
		//$this->_bbcodes['display']['url']['bbcode_replace']			= '<a href="{option}">{content}</a>';
		//$this->_bbcodes['display']['url']['bbcode_php_plugin']		= '';
		
		$this->_bbcodes['display']['img']['bbcode_replace']			= '<img src="{content}" />';
		$this->_bbcodes['display']['img']['bbcode_php_plugin']		= '';
		
		$this->_bbcodes['display']['background']['bbcode_replace']	= '<font background="{option}">{content}</font>';
		$this->_bbcodes['display']['size']['bbcode_replace']		= '<font size="{option}">{content}</font>';
		$this->_bbcodes['display']['size']['bbcode_php_plugin']		= '';
		$this->_bbcodes['display']['color']['bbcode_replace']		= '<font color="{option}">{content}</font>';
		$this->_bbcodes['display']['font']['bbcode_replace']		= '<font face="{option}">{content}</font>';

		//-----------------------------------------
		// and actually replace
		//-----------------------------------------

		$t = $this->preDisplayParse( $t );

		//-----------------------------------------
		// And then return teh code
		//-----------------------------------------
		
		if( is_array( $this->noParseStorage ) AND count( $this->noParseStorage ) )
		{
			foreach( $this->noParseStorage as $replacement )
			{
				$t = str_replace( $replacement['find'], $replacement['replace'], $t );
			}
		}
		
		$this->noParseStorage	= array();
		
		//-----------------------------------------
		// Put our codeboxes back now
		//-----------------------------------------
		
		foreach( $_codeboxes as $_increment => $_replacement )
		{
			$_replacement	= $this->unconvertSmilies( $_replacement );
			
			$t = str_replace( "__CODEBOX_{$_increment}__", $_replacement, $t );
		}

		//-----------------------------------------
		// Fix some special characters
		//-----------------------------------------

		$t = str_replace( '&#39;'  , "'", $t );
		$t = str_replace( '&#33;'  , "!", $t );
		$t = str_replace( '&#039;' , "'", $t );
		$t = str_replace( '&apos;' , "'", $t );

		//-----------------------------------------
		// Remove all macros
		//-----------------------------------------
		
		$t = preg_replace( "#<\{.+?\}>#", "", $t );

		//-----------------------------------------
		// Reset the bbcodes to be safe
		//-----------------------------------------
		
		$this->initOurBbcodes();
		
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_isForRte', false );
		
		return $t;
	}
	
	/**
	 * Unconvert smilies
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public function unconvertSmilies( $txt )
	{
		//-----------------------------------------
		// Now in core.php
		//-----------------------------------------

		return IPSLib::unconvertSmilies( $txt );
	}
	
	/**
	 * Remove raw smilies
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Text with smiley codes removed
	 */
	public function stripEmoticons( $txt )
	{
		$codes_seen		= array();
		
		if ( count( $this->cache->getCache('emoticons') ) > 0 )
		{
			foreach( $this->cache->getCache('emoticons') as $row )
			{
				if ( is_array($this->registry->output->skin) AND $this->registry->output->skin['set_emo_dir'] AND $row['emo_set'] != $this->registry->output->skin['set_emo_dir'] )
				{
					continue;
				}
				
				$code	= $row['typed'];
				
				if ( in_array( $code, $codes_seen ) )
				{
					continue;
				}
				
				$codes_seen[] = $code;
									
				//-----------------------------------------
				// Now, check for the html safe versions
				//-----------------------------------------	
				
				$_emoCode			= str_replace( '<', '&lt;', str_replace( '>', '&gt;', $code ) );	
				$_emoImage			= $row['image'];
				$emoPosition		= 0;
				$invalidWrappers	= "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

				while( ( $position = strpos( $txt, $_emoCode, $emoPosition ) ) !== false )
				{
					if( strpos( $invalidWrappers, substr( $txt, $position-1, 1 ) ) === false AND strpos( $invalidWrappers, substr( $txt, ($position + strlen($_emoCode)), 1 ) ) === false )
					{
						$txt 		= substr_replace( $txt, '', $position, strlen($_emoCode) );
						
						$position	+= strlen($replace);
					}

					$emoPosition	= $position + 1;
					
					if( $emoPosition > strlen($txt) )
					{
						break;
					}
				}
			}
		}
		
		return $txt;
	}

	/**
	 * Parse the post to store in the database
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public function preDbParse( $txt="" )
	{
		//-----------------------------------------
		// Reset
		//-----------------------------------------
		
		$this->_resetPointers();
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_media', 0 );
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_images', 0 );

		//-----------------------------------------
		// Remove session id's from any post
		//-----------------------------------------

		//$txt = htmlspecialchars( $txt, ENT_NOQUOTES );
		$txt = preg_replace_callback( "#(\?|&amp;|;|&)s=([0-9a-zA-Z]){32}(&amp;|;|&|$)?#", array( $this, '_bashSession' ), $txt );
		
		//-----------------------------------------
		// convert <br> to \n
		//-----------------------------------------
		
		if ( ! $this->parse_nl2br )
		{
			$txt = str_replace( "\n", "", $txt );
		}

		$txt = str_ireplace( array( '<br>', '<br />' ), "\n", $txt );

		//-----------------------------------------
		// Protect against LTR/RTL swopping
		//-----------------------------------------
		
		$txt	= str_replace( "&#8234;", '', $txt );
		$txt	= str_replace( "&#8235;", '', $txt );
		$txt	= str_replace( "&#8236;", '', $txt );
		$txt	= str_replace( "&#8237;", '', $txt );
		$txt	= str_replace( "&#8238;", '', $txt );

		//-----------------------------------------
		// Swap \n back to <br>
		//-----------------------------------------
		
		$txt = nl2br( $txt );
		
		//-----------------------------------------
		// Are we parsing bbcode?
		//-----------------------------------------
		
		if ( $this->parse_bbcode )
		{
			$txt	= $this->parseBbcode( $txt, 'db' );
		}

		//-----------------------------------------
		// Unicode (on by default)?
		// @see initdata.php IPS_ALLOW_UNICODE constant
		//-----------------------------------------
		
		if ( $this->allow_unicode )
		{
			$txt = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $txt );
		}
		
		/* Parse smililes before bbcode, and remove elemnts that will not be parsed on display. Bug Fix: #20964 */
		// Parsing here causes nonparsed stuff to be double stored in the storeNonParsed array.  This causes problems
		// when you limit images per post.  
		// @link	http://community.invisionpower.com/tracker/issue-23024-images-per-post-not-working
		// $txt = $this->_storeNonParsed( $txt, 'display' );
		
		//-----------------------------------------
		// Parse smilies
		//-----------------------------------------

		if ( $this->parse_smilies )
		{
			$codes_seen		= array();
        
			if ( count( $this->cache->getCache('emoticons') ) > 0 )
			{
				foreach( $this->cache->getCache('emoticons') as $row )
				{
					if ( is_array($this->registry->output->skin) AND $this->registry->output->skin['set_emo_dir'] AND $row['emo_set'] != $this->registry->output->skin['set_emo_dir'] )
					{
						continue;
					}
        
					$code	= $row['typed'];
        
					if ( in_array( $code, $codes_seen ) )
					{
						continue;
					}
        
					$codes_seen[] = $code;
        
					//-----------------------------------------
					// Now, check for the html safe versions
					//-----------------------------------------	
        
					$_emoCode			= str_replace( '<', '&lt;', str_replace( '>', '&gt;', $code ) );	
					$_emoImage			= $row['image'];
					$emoPosition		= 0;
        
					//-----------------------------------------
					// These are chars that can't surround the emo
					//-----------------------------------------
        
					$invalidWrappers	= "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'\"";
        
					//-----------------------------------------
					// Have any more chars to look at?
					//-----------------------------------------
        
					while( ( $position = stripos( $txt, $_emoCode, $emoPosition ) ) !== false )
					{
						//-----------------------------------------
						// Are we at the start of the string, or
						// is the preceeding char not an invalid wrapper?
						//-----------------------------------------
        
						if( ( $position === 0 OR stripos( $invalidWrappers, substr( $txt, $position-1, 1 ) ) === false ) 
        
						//-----------------------------------------
						// Are we at the end of the string or is the
						// next char not an invalid wrapper?
						//-----------------------------------------
        
						AND ( strlen($txt) == ($position + strlen($_emoCode)) OR stripos( $invalidWrappers, substr( $txt, ($position + strlen($_emoCode)), 1 ) ) === false ) )
						{
							//-----------------------------------------
							// Replace the emoticon and increment position counter
							//-----------------------------------------

							$replace	= $this->_retrieveSmiley( $_emoCode, $_emoImage );
							$txt 		= substr_replace( $txt, $replace, $position, strlen($_emoCode) );
        
							$position	+= strlen($replace);
						}
        
						$emoPosition	= $position + 1;
        
						if( $emoPosition > strlen($txt) )
						{
							break;
						}
					}
				}
			}

			if ( $this->settings['max_emos'] )
			{
				if ( $this->emoticon_count > $this->settings['max_emos'] )
				{
					$this->error = 'too_many_emoticons';
				}
			}
			
			/* Put alt tags in */
			if( is_array( $this->emoticon_alts ) && count( $this->emoticon_alts ) )
			{
				foreach( $this->emoticon_alts as $r )
				{
					$txt = str_replace( $r[0], $r[1], $txt );
				}
			}
		}
		
		/* Put back non parsed code.  Bug Fix: #20964 */
		if( is_array( $this->noParseStorage ) && count( $this->noParseStorage ) )
		{
			foreach( $this->noParseStorage as $r )
			{
				$txt = str_replace( $r['find'], $r['replace'], $txt );
			}
		}
		
		//-----------------------------------------
		// Badwords
		//-----------------------------------------
		
		$txt = $this->badWords($txt);

		return $txt;
	}

	/**
	 * This function processes the DB post before printing as output
	 *
	 * @access	public
	 * @param	string			Raw text
	 * @return	string			Converted text
	 */
	public function preDisplayParse( $txt="" )
	{
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_media', 0 );
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_images', 0 );
		
		if ( $this->parse_html )
		{
			//-----------------------------------------
			// Store true line breaks first
			//-----------------------------------------
			
			if ( $this->parse_nl2br )
			{
				$txt = str_replace( '<br />', "~~~~~_____~~~~~", $txt );
			}
			
			$txt = $this->_parseHtml( $txt );
		}

		//-----------------------------------------
		// Fix "{style_images_url}"
		//-----------------------------------------
		
		$txt = str_replace( "{style_images_url}", "&#123;style_images_url&#125;", $txt );

		//-----------------------------------------
		// Custom BB code
		//-----------------------------------------
		
		$_NOW = IPSDebug::getMemoryDebugFlag();

		if ( $this->parse_bbcode  )
		{
			$txt = $this->parseBbcode( $txt, 'display' );
		}

		IPSDebug::setMemoryDebugFlag( "PreDisplayParse - parsed BBCode", $_NOW );
		
		$_NOW = IPSDebug::getMemoryDebugFlag();
		
		if ( $this->parse_wordwrap > 0 )
		{
			$txt = $this->applyWordwrap( $txt, $this->parse_wordwrap );
		}
		
		IPSDebug::setMemoryDebugFlag( "PreDisplayParse - applied wordwrap", $_NOW );
	
		//-----------------------------------------
		// Fix line breaks
		//-----------------------------------------
		
		if ( $this->parse_html )
		{
			$txt = str_replace( "~~~~~_____~~~~~", '<br />', $txt );
		}
				
		//-----------------------------------------
		// And fix old youtube embedded videos..
		//-----------------------------------------
		
		/*if( stripos( $txt, "<object" ) AND stripos( $txt, "<embed" ) )
		{
			//$txt = preg_replace( "#<object(.+?)<embed(.+?)></embed></object>#i", "<embed\\2</embed>", $txt );
			$txt = preg_replace( "#<object(.+?)<embed.+?></embed></object>#i", "<object\\1</object>", $txt );
		}*/

		return $txt;
	}

	/**
	 * This function processes the text before showing for editing, etc
	 *
	 * @access	public
	 * @param	string			Raw text
	 * @return	string			Converted text
	 */
	public function preEditParse($txt="")
	{
		//-----------------------------------------
		// Clean up BR tags
		//-----------------------------------------

		if ( !$this->parse_html OR $this->parse_nl2br )
		{
			$txt = str_replace( "\n"  	, ""	, $txt );
			$txt = str_replace( "<br>"  , "\n"	, $txt );
			$txt = str_replace( "<br />", "\n"	, $txt );
		}
		
		//-----------------------------------------
		// Unconvert smilies
		//-----------------------------------------
		
		$txt = $this->unconvertSmilies( $txt );

		//-----------------------------------------
		// Clean up nbsp
		//-----------------------------------------
		
		$txt = str_replace( '&nbsp;&nbsp;&nbsp;&nbsp;', "\t", $txt );
		$txt = str_replace( '&nbsp;&nbsp;'            , "  ", $txt );

		//-----------------------------------------
		// Parse html
		//-----------------------------------------
		
		if ( $this->parse_html )
		{
			$txt = str_replace( "&#39;", "'", $txt);
		}
		
		//-----------------------------------------
		// Fix "{style_images_url}"
		//-----------------------------------------
		
		$txt = str_replace( "{style_images_url}", "&#123;style_images_url&#125;", $txt );
		
		return trim( stripslashes( $txt ) );
	}
	

	/**
	 * Retrieve the proper emoticon image code
	 *
	 * @access	private
	 * @param	string		Emoticon code we are replacing (i.e. :D)
	 * @param	string		Emoticon image to display (i.e. 'biggrin.png')
	 * @return	string		Converted text
	 */
	private function _retrieveSmiley( $_emoCode, $_emoImage )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		if ( ! $_emoCode or ! $_emoImage )
		{
			return '';
		}

		$this->emoticon_count++;
		
		$this->emoticon_alts[] = array( "#EMO_ALT_{$this->emoticon_count}#", $_emoCode );
		
		//-----------------------------------------
		// Return
		//-----------------------------------------
		
		return "<img src='" . $this->settings['emoticons_url'] . "/{$_emoImage}' class='bbc_emoticon' alt='#EMO_ALT_{$this->emoticon_count}#' />";
	}
	
	/**
	 * Loop over the bbcode and make replacements as necessary
	 *
	 * @access	public
	 * @param	string		Current text
	 * @param	string		[db|display] Current method to parse
	 * @param 	mixed		[optional] Only parse the selected code(s)
	 * @return	string		Converted text
	 */
	public function parseBbcode( $txt, $cur_method='db', $_code=null )
	{
		//-----------------------------------------
		// Pull out the non-replacable codes
		//-----------------------------------------

		if( !is_string($_code) )
		{
			$txt = $this->_storeNonParsed( $txt, $cur_method );
		}

		//-----------------------------------------
		// Regular replacing
		//-----------------------------------------		

		if( isset( $this->_bbcodes[ $cur_method ] ) AND is_array($this->_bbcodes[ $cur_method ]) AND count($this->_bbcodes[ $cur_method ]) )
		{
			foreach( $this->_bbcodes[ $cur_method ] as $_bbcode )
			{
				//-----------------------------------------
				// Can this group use this bbcode?
				//-----------------------------------------
				
				if( $_bbcode['bbcode_groups'] != 'all' AND $this->parsing_mgroup )
				{
					$pass		= false;
					$groups		= array_diff( explode( ',', $_bbcode['bbcode_groups'] ), array('') );
					$mygroups	= array( $this->parsing_mgroup );
					
					if( $this->parsing_mgroup_others )
					{
						$mygroups	= array_diff( array_merge( $mygroups, explode( ',', IPSText::cleanPermString( $this->parsing_mgroup_others ) ) ), array('') );
					}

					foreach( $groups as $g_id )
					{
						if( in_array( $g_id, $mygroups ) )
						{
							$pass = true;
							break;
						}
					}
					
					if( !$pass )
					{
						continue;
					}
				}

				//-----------------------------------------
				// Reset our current position
				//-----------------------------------------
				
				$this->cur_pos = 0;
				
				//-----------------------------------------
				// Store teh tags
				//-----------------------------------------
				
				$_tags = array( $_bbcode['bbcode_tag'] );

				//-----------------------------------------
				// We'll also need to check for any aliases
				//-----------------------------------------
				
				if( $_bbcode['bbcode_aliases'] )
				{
					$aliases = explode( ',', trim($_bbcode['bbcode_aliases']) );
					
					if( is_array($aliases) AND count($aliases) )
					{
						foreach( $aliases as $alias )
						{
							$_tags[]	= trim($alias);
						}
					}
				}

				//-----------------------------------------
				// If we have a plugin, just pass off
				//-----------------------------------------

				if( $_bbcode['bbcode_php_plugin'] )
				{
					//-----------------------------------------
					// Are we only parsing one code?
					//-----------------------------------------
					
					if( is_array($_code) )
					{
						$good	= false;
						
						foreach( $_tags as $_tag )
						{
							if( in_array( $_tag, $_code ) )
							{
								$good	= true;
							}
						}
						
						if( !$good )
						{
							continue;
						}
					}
					else if( is_string($_code) )
					{
						$good	= false;
						
						foreach( $_tags as $_tag )
						{
							if( $_tag == $_code )
							{
								$good	= true;
							}
						}
						
						if( !$good )
						{
							continue;
						}
					}
					
					$_key	= md5($_bbcode['bbcode_tag']);
					
					//-----------------------------------------
					// Do we already have this plugin in our registry?
					//-----------------------------------------
					
					if( isset($this->plugins[ $_key ]) )
					{
						$method	= "pre" . ucwords($cur_method) . "Parse";
						
						//-----------------------------------------
						// Run the method if it exists
						//-----------------------------------------
						
						if( method_exists( $this->plugins[ $_key ], $method ) )
						{
							$_original	= $txt;
							$txt		= $this->plugins[ $_key ]->$method( $txt );

							if( !$txt )
							{
								$txt = $_original;
							}
							else if( $this->plugins[ $_key ]->error )
							{
								$this->error	= $this->plugins[ $_key ]->error;
								return $txt;
							}
							else if( $this->plugins[ $_key ]->warning )
							{
								$this->warning	= $this->plugins[ $_key ]->warning;
							}
						}
					}

					//-----------------------------------------
					// First time we've called this plugin
					//-----------------------------------------
					
					else if( file_exists( IPS_ROOT_PATH . 'sources/classes/bbcode/custom/' . $_bbcode['bbcode_php_plugin'] ) )
					{
						require_once( IPS_ROOT_PATH . 'sources/classes/bbcode/custom/' . $_bbcode['bbcode_php_plugin'] );
						
						$_classname = 'bbcode_' . IPSText::alphanumericalClean( $_bbcode['bbcode_tag'] );
						
						//-----------------------------------------
						// Class we need exists
						//-----------------------------------------
						
						if( class_exists( $_classname ) )
						{
							//-----------------------------------------
							// New instance of class, store in plugin registry for use next time
							//-----------------------------------------
							
							$plugin = new $_classname( $this->registry, $this );
							$method	= "pre" . ucwords($cur_method) . "Parse";
							
							$this->plugins[ md5($_bbcode['bbcode_tag']) ]	= $plugin;

							//-----------------------------------------
							// Method we need exists
							//-----------------------------------------
							
							if( method_exists( $plugin, $method ) )
							{
								$_original	= $txt;
								$txt		= $plugin->$method( $txt );

								if( !$txt )
								{
									$txt = $_original;
								}
								else if( $plugin->error )
								{
									$this->error	= $plugin->error;
									return $txt;
								}
								else if( $plugin->warning )
								{
									$this->warning	= $plugin->warning;
								}
							}
						}
					}

					//-----------------------------------------
					// When we run a plugin, we don't do any other processing "automatically".
					// Plugin is capable of doing what it wants that way.
					//-----------------------------------------

					continue;
				}

				//-----------------------------------------
				// Loop over this bbcode's tags
				//-----------------------------------------

				foreach( $_tags as $_tag )
				{ 
					//-----------------------------------------
					// Are we only parsing one code?
					//-----------------------------------------
					
					if( is_array($_code) AND !in_array( $_tag, $_code ) )
					{
						continue;
					}
					else if( is_string($_code) AND $_tag != $_code )
					{
						continue;
					}
					
					//-----------------------------------------
					// Infinite loop catcher
					//-----------------------------------------
					
					$_iteration	= 0;
					
					//-----------------------------------------
					// Start building open tag
					//-----------------------------------------
					
					$open_tag = '[' . $_tag;

					//-----------------------------------------
					// Doz I can haz opin tag? Loopy loo
					//-----------------------------------------
					
					while( ( $this->cur_pos = stripos( $txt, $open_tag, $this->cur_pos ) ) !== false )
					{
						//-----------------------------------------
						// Stop infinite loops
						//-----------------------------------------
						
						if( $_iteration > $this->settings['max_bbcodes_per_post'] )
						{
							break;
						}
						
						$_iteration++;
						
						$open_length = strlen($open_tag);

						//-----------------------------------------
						// Grab the new position to jump to
						//-----------------------------------------
						
						$new_pos = strpos( $txt, ']', $this->cur_pos ) ? strpos( $txt, ']', $this->cur_pos ) : $this->cur_pos + 1;

						//-----------------------------------------
						// Extract the option (like surgery)
						//-----------------------------------------
	
						$_option	= '';
						
						if( $_bbcode['bbcode_useoption'] )
						{
							//-----------------------------------------
							// Is option optional?
							//-----------------------------------------
						
							if( $_bbcode['bbcode_optional_option'] )
							{
								//-----------------------------------------
								// Does we haz it?
								//-----------------------------------------
						
								if( substr( $txt, $this->cur_pos + strlen($open_tag), 1 ) == '=' )
								{
									$open_length	+= 1;
									$_option		= substr( $txt, $this->cur_pos + $open_length, (strpos( $txt, ']', $this->cur_pos ) - ($this->cur_pos + $open_length)) );
								}
								
								//-----------------------------------------
								// If not, [u] != [url] (for example)
								//-----------------------------------------
						
								else if( (strpos( $txt, ']', $this->cur_pos ) - ( $this->cur_pos + $open_length )) !== 0 )
								{
									if( strpos( $txt, ']', $this->cur_pos ) )
									{
										$this->cur_pos = $new_pos;
										continue;
									}
									else
									{
										break;
									}
								}
							}
							
							//-----------------------------------------
							// No?  Then just grab it
							//-----------------------------------------
							
							else
							{
								$open_length	+= 1;
								$_option		= substr( $txt, $this->cur_pos + $open_length, (strpos( $txt, ']', $this->cur_pos ) - ($this->cur_pos + $open_length)) );
							}
						}
						
						//-----------------------------------------
						// [img] != [i] (for example)
						//-----------------------------------------
						
						else if( (strpos( $txt, ']', $this->cur_pos ) - ( $this->cur_pos + $open_length )) !== 0 )
						{
							if( strpos( $txt, ']', $this->cur_pos ) )
							{
								$this->cur_pos = $new_pos;
								continue;
							}
						}
						
						//-----------------------------------------
						// Protect against XSS
						//-----------------------------------------
						
						$_optionStrLen	= IPSText::mbstrlen( $_option );
						$_optionSlenstr	= strlen($_option);
						$_option		= $this->checkXss($_option, false, $_tag);

						/* Not parsing URls? */
						if ( isset( $_bbcode['bbcode_no_auto_url_parse'] ) AND $_bbcode['bbcode_no_auto_url_parse'] )
						{
							$_option = preg_replace( "#(http|https|news|ftp)://#i", "\\1&#58;//", $_option );
						}
						
						if ( $_option !== FALSE )
						{
							//-----------------------------------------
							// If this is a single tag, that's it
							//-----------------------------------------
							
							if( $_bbcode['bbcode_single_tag'] )
							{
								$txt = substr_replace( $txt, $this->_bbcodeToHtml( $_bbcode, $_option, '' ), $this->cur_pos, ($open_length + $_optionSlenstr + 1) );
							}
							
							//-----------------------------------------
							// Otherwise replace out the content too
							//-----------------------------------------
							
							else
							{
								$close_tag	= '[/' . $_tag . ']';
	
								if( stripos( $txt, $close_tag, $new_pos ) !== false )
								{
									$_content	= substr( $txt, ($this->cur_pos + $open_length + $_optionSlenstr + 1), (stripos( $txt, $close_tag, $this->cur_pos ) - ($this->cur_pos + $open_length + $_optionSlenstr + 1)) );
									
									if( $_bbcode['bbcode_useoption'] AND $_bbcode['bbcode_optional_option'] AND !$_option )
									{
										$_option	= $_content;
										$_option	= $this->checkXss($_option, false, $_tag);
									}

									/* Not parsing URls? */
									if ( isset( $_bbcode['bbcode_no_auto_url_parse'] ) AND $_bbcode['bbcode_no_auto_url_parse'] )
									{
										$_content = preg_replace( "#(http|https|news|ftp)://#i", "\\1&#58;//", $_content );
									}
						
									$txt		= substr_replace( $txt, $this->_bbcodeToHtml( $_bbcode, $_option /*? $_option : $_content*/, $_content ), $this->cur_pos, (stripos( $txt, $close_tag, $this->cur_pos ) + strlen($close_tag) - $this->cur_pos) );	
								}
								else
								{
									//-----------------------------------------
									// If there's no close tag, no need to continue
									//-----------------------------------------
									
									break;
								}
							}
						}
						
						//-----------------------------------------
						// And reset current position to end of open tag
						// Bug 14744 - if we jump to $new_pos it can skip the opening of the next bbcode tag
						// when the replacement HTML is shorter than the full bbcode representation...
						//-----------------------------------------

						$this->cur_pos = stripos( $txt, $open_tag ) ? stripos( $txt, $open_tag ) : $this->cur_pos + 1; //$new_pos;

						if( $this->cur_pos > strlen($txt) )
						{
							break;
						}
					}
				}
			}
		}

		//-----------------------------------------
		// (c) (r) and (tm)
		//-----------------------------------------
		
		if( $cur_method == 'display' AND $_code !== 'code' )
		{
			$txt = str_ireplace( "(c)"	, "&copy;"	, $txt );
			$txt = str_ireplace( "(tm)"	, "&#153;"	, $txt );
			$txt = str_ireplace( "(r)"	, "&reg;"	, $txt );
		}
		
		//-----------------------------------------
		// And finally replace those bbcodes
		//-----------------------------------------

		if( !$_code )
		{
			$txt = $this->_parseNonParsed( $txt, $cur_method );
		}
		
		//-----------------------------------------
		// Auto parse URLs (only if this is full sweep)
		//-----------------------------------------

		if( !$_code AND $cur_method == 'display' )
		{
			/* Capture 'href="' and '</a>' as [URL] is now parsed first, we discard these in _autoParseUrls */
			/**
			 * @link	http://community.invisionpower.com/tracker/issue-23726-parser-wrong-url-with-unicode-chars/
			 * I had to add the /u modifier to correct this.  Previously, the first byte sequence of the word was matching \s.
			 */
			$opts = ( IPS_DOC_CHAR_SET == 'UTF-8' ) ? 'isu' : 'is';
			$txt  = preg_replace_callback( "#(^|\s|\)|\(|\{|\}|>|\]|\[|href=\S)((http|https|news|ftp)://(?:[^<>\)\[\"\s]+|[a-zA-Z0-9/\._\-!&\#;,%\+\?:=]+))(</a>)?#" . $opts, array( $this, '_autoParseUrls' ), $txt );
		}

		return $txt;
	}
	
	/**
	 * This is a utility function to finish parsing the "non-parsed" items, such as image and code tags, when we are only
	 * parsing one tag at a time.  For instance, see the parsePollTags method of han_parse_bbcode.php
	 *
	 * @see		parseBbcode::parsePollTags()
	 * @access	public
	 * @param	string		BBCode with non-parse markers
	 * @param	string		Current method
	 * @param	string		BBCode with non-parse markers replaced
	 */
	public function finishNonParsed( $txt, $cur_method='db' )
	{
		return $this->_parseNonParsed( $txt, $cur_method );
	}
	
	/**
	 * Does the actual bbcode replacement
	 *
	 * @access	private
	 * @param	string		Current bbcode to parse
	 * @param	string		[Optional] Option text
	 * @param	string		[Optional for single tag bbcodes] Content text
	 * @return	string		Converted text
	 */
	private function _bbcodeToHtml( $_bbcode, $option='', $content='' )
	{
		//-----------------------------------------
		// Strip the optional quote delimiters
		//-----------------------------------------

		$option			= str_replace( '&quot;', '"', $option );
		$option			= str_replace( '&#39;', "'", $option );
		$option			= trim( $option, '"' . "'" );

		//-----------------------------------------
		// Stop CSS injection
		//-----------------------------------------
		
		if( $option )
		{
			//-----------------------------------------
			// Cut off for entities in option
			// @see	http://community.invisionpower.com/tracker/issue-19958-acronym/
			//-----------------------------------------
			
			$option	= IPSText::UNhtmlspecialchars( $option );
			$option	= str_replace( '&#33;', '!', $option );
			
			if( strpos( $option, ';' ) !== false )
			{
				$option = substr( $option, 0, strpos( $option, ';' ) );
			}
			
			$option	= IPSText::htmlspecialchars( $option );
			$option	= str_replace( '!', '&#33;', $option );
		}
						
		$option			= str_replace( '"', '&quot;', $option );
		$option			= str_replace( "'", '&#39;', $option );

		//-----------------------------------------
		// Swapping option/content?
		//-----------------------------------------
		
		if( $_bbcode['bbcode_switch_option'] )
		{
			$_tmp		= $content;
			$content	= $option;
			$option		= $_tmp;
		}

		//-----------------------------------------
		// Replace
		//-----------------------------------------
		
		$replaceCode	= $_bbcode['bbcode_replace'];
		$replaceCode	= str_replace( '{base_url}', $this->settings['board_url'] . '/index.php?', $replaceCode );
		$replaceCode	= str_replace( '{image_url}', $this->settings['img_url'], $replaceCode );
		
		preg_match( '/\{text\.(.+?)\}/i', $replaceCode, $matches );
		
		if( is_array($matches) AND count($matches) )
		{
			$replaceCode = str_replace( $matches[0], $this->lang->words[ $matches[1] ], $replaceCode );
		}
		
		$replaceCode	= str_replace( '{option}', $option, $replaceCode );
		$replaceCode	= str_replace( '{content}', $content, $replaceCode );
		
		//-----------------------------------------
		// Fix linebreaks in textareas
		//-----------------------------------------
		
		if( stripos( $replaceCode, "<textarea" ) !== false )
		{
			$replaceCode = str_replace( '<br />', "", $replaceCode );
			$replaceCode = str_replace( "\r", "", $replaceCode );
			$replaceCode = str_replace( "\n", "<br />", $replaceCode );
		}

		return $replaceCode;
	}
	
	/**
	 * Parse escaped HTML for display
	 *
	 * @access	private
	 * @param	string		Current text to parse
	 * @return	string		Converted text
	 */
	private function _parseHtml( $txt="" )
	{
		if ( $txt == "" )
		{
			return $txt;
		}
		
		//-----------------------------------------
		// <br>s are &lt;br&gt; at this point :)
		//-----------------------------------------

		if ( !$this->parse_nl2br )
		{
			$txt = str_replace( "\n", '', $txt );
			$txt = str_replace( "<br>"	, "\n" , $txt );
			$txt = str_replace( "<br />", "\n" , $txt );
		}

		$txt = str_replace( "&#39;"	, "'", $txt );
		$txt = str_replace( "&#33;"	, "!", $txt );
		$txt = str_replace( "&#036;", "$", $txt );
		$txt = str_replace( "&#124;", "|", $txt );
		$txt = str_replace( "&amp;"	, "&", $txt );
		$txt = str_replace( "&gt;"	, ">", $txt );
		$txt = str_replace( "&lt;"	, "<", $txt );
		$txt = str_replace( "&quot;", '"', $txt );

		return $txt;
	}
	
	/**
	 * Store the bbcode content that should not have inner content parsed
	 *
	 * @access	protected
	 * @param	string		Current text
	 * @param	string		[db|display] Current method to parse
	 * @return	string		Converted text
	 */
	protected function _storeNonParsed( $txt, $cur_method='db' )
	{
		//-----------------------------------------
		// Pull out the non-replacable codes
		//-----------------------------------------

		if( isset( $this->_bbcodes[ $cur_method ] ) AND is_array($this->_bbcodes[ $cur_method ]) AND count($this->_bbcodes[ $cur_method ]) )
		{
			foreach( $this->_bbcodes[ $cur_method ] as $_bbcode )
			{
				//-----------------------------------------
				// Are we parsing inside?
				//-----------------------------------------

				if( !$_bbcode['bbcode_no_parsing'] )
				{
					continue;
				}
				
				//-----------------------------------------
				// If not, pull out and store for later
				//-----------------------------------------
				
				$_curPosition 	= 0;
				
				$_tags = array( $_bbcode['bbcode_tag'] );

				//-----------------------------------------
				// We'll also need to check for any aliases
				//-----------------------------------------
				
				if( $_bbcode['bbcode_aliases'] )
				{
					$aliases = explode( ',', trim($_bbcode['bbcode_aliases']) );
					
					if( is_array($aliases) AND count($aliases) )
					{
						foreach( $aliases as $alias )
						{
							$_tags[]	= trim($alias);
						}
					}
				}

				//-----------------------------------------
				// Loop over this bbcode's tags
				//-----------------------------------------
				
				foreach( $_tags as $_tag )
				{
					//-----------------------------------------
					// Start building open tag
					//-----------------------------------------
					
					$open_tag = '[' . $_tag;

					//-----------------------------------------
					// Doz I can haz opin tag? Loopy loo
					//-----------------------------------------
					
					while( $_curPosition <= strlen($txt) AND ( $_curPosition = stripos( $txt, $open_tag, $_curPosition ) ) !== false )
					{
						$open_length	= strlen($open_tag);
						
						$this->_storedNoParsing++;
						$_thisTag		= "<!--NoParse{$this->_storedNoParsing}-->";

						//-----------------------------------------
						// Extract the option (like surgery)
						//-----------------------------------------
	
						$_option	= '';
						
						if( $_bbcode['bbcode_useoption'] )
						{
							//-----------------------------------------
							// Is option optional?
							//-----------------------------------------
						
							if( $_bbcode['bbcode_optional_option'] )
							{
								//-----------------------------------------
								// Does we haz it?
								//-----------------------------------------
						
								if( substr( $txt, $_curPosition + strlen($open_tag), 1 ) == '=' )
								{
									$open_length	+= 1;
									$_option		= substr( $txt, $_curPosition + $open_length, (strpos( $txt, ']', $_curPosition ) - ($_curPosition + $open_length)) );
								}
								
								//-----------------------------------------
								// If not, [u] != [url] (for example)
								//-----------------------------------------
						
								else if( (strpos( $txt, ']', $_curPosition ) - ( $_curPosition + $open_length )) !== 0 )
								{
									$_curPosition = strpos( $txt, ']', $_curPosition );
									continue;
								}
							}
							
							//-----------------------------------------
							// No?  Then just grab it
							//-----------------------------------------
							
							else
							{
								$open_length	+= 1;
								$_option		= substr( $txt, $_curPosition + $open_length, (strpos( $txt, ']', $_curPosition ) - ($_curPosition + $open_length)) );
							}
						}
						
						//-----------------------------------------
						// [img] != [i] (for example)
						//-----------------------------------------
						
						else if( (strpos( $txt, ']', $_curPosition ) - ( $_curPosition + $open_length )) !== 0 )
						{
							$_curPosition = ( strpos( $txt, ']', $_curPosition ) !== false ) ? strpos( $txt, ']', $_curPosition ) : $_curPosition + 1;
							continue;
						}
	
						//-----------------------------------------
						// Grab the new position to jump to
						//-----------------------------------------
						
						$new_pos = ( strpos( $txt, ']', $_curPosition ) !== false ) ? strpos( $txt, ']', $_curPosition ) : $_curPosition + 1;
	
						//-----------------------------------------
						// If this is a single tag, that's it
						//-----------------------------------------
						
						if( $_bbcode['bbcode_single_tag'] )
						{
							$_currentContent	= substr( $txt, $_curPosition, ($open_length + IPSText::mbstrlen($_option) + 1) );
							$txt 				= substr_replace( $txt, $_thisTag, $_curPosition, ($open_length + IPSText::mbstrlen($_option) + 1) );
						}
						
						//-----------------------------------------
						// Otherwise replace out the content too
						//-----------------------------------------
						
						else
						{
							$close_tag	= '[/' . $_tag . ']';

							if( stripos( $txt, $close_tag, $new_pos ) !== false )
							{
								$_currentContent	= substr( $txt, $_curPosition, (stripos( $txt, $close_tag, $_curPosition ) + strlen($close_tag) - $_curPosition) );
								$txt				= substr_replace( $txt, $_thisTag, $_curPosition, (stripos( $txt, $close_tag, $_curPosition ) + strlen($close_tag) - $_curPosition) );	
							}
							else
							{
								$_curPosition = $new_pos;
								continue;
							}
						}
	
						$this->noParseStorage[$this->_storedNoParsing] = array(
														'find'		=> $_thisTag,
														'replace'	=> $_currentContent,
														'code'		=> $_tag,
														);
												
						//-----------------------------------------
						// And reset current position to end of open tag
						//-----------------------------------------
	
						$_curPosition = $new_pos;
					}
				}
			}
		}

		return $txt;
	}
	
	/**
	 * Parse the stored non-inner parsed bbcode
	 *
	 * @access	protected
	 * @param	string		Current text
	 * @param	string		[db|display] Current method to parse
	 * @return	string		Converted text
	 */
	protected function _parseNonParsed( $txt, $cur_method='db' )
	{
		if( is_array( $this->noParseStorage ) AND count( $this->noParseStorage ) )
		{
			$this->resetPerPost();

			foreach( $this->noParseStorage as $replacement )
			{
				//-----------------------------------------
				// Fix HTML in code tags
				//-----------------------------------------
				
				if( $this->parse_html )
				{
					//$replacement['replace'] = str_replace( "", "&amp;"	, $replacement['replace'] );
					$replacement['replace'] = str_replace( "&", "&amp;"	, $replacement['replace'] );
					$replacement['replace'] = str_replace( "'", "&#39;"	, $replacement['replace'] );
					$replacement['replace'] = str_replace( "!", "&#33;"	, $replacement['replace'] );
					$replacement['replace'] = str_replace( "$", "&#036;", $replacement['replace'] );
					$replacement['replace'] = str_replace( "|", "&#124;", $replacement['replace'] );
					$replacement['replace'] = str_replace( ">", "&gt;"	, $replacement['replace'] );
					$replacement['replace'] = str_replace( "<", "&lt;"	, $replacement['replace'] );
					$replacement['replace'] = str_replace( '"', "&quot;", $replacement['replace'] );
					$replacement['replace'] = str_replace( '&amp;#60;', "&lt;", $replacement['replace'] );
					$replacement['replace'] = str_replace( '&amp;#092;', "&#092;", $replacement['replace'] );
					//$replacement['replace'] = str_replace( '&lt;br /&gt;', "<br />", $replacement['replace'] );
				}

				$_final = $this->parseBbcode( $replacement['replace'], $cur_method, $replacement['code'] );
				$_final	= $this->preEditParse( $_final );
				
				/* Bug fix: #20973 */
				if( preg_match( "/\<\!\-\-NoParse(\d+)\-\-\>/", $_final, $matches ) )
				{
					if( $matches[1] )
					{
						$_final = str_replace( "<!--NoParse{$matches[1]}-->", $this->noParseStorage[ $matches[1] ]['replace'], $_final );
						unset( $this->noParseStorage[ $matches[1] ] );
					}
				}

				//-----------------------------------------
				// We don't want any bbcodes to parse..
				//-----------------------------------------
				
				$_final = str_replace( "[", "&#91;"  , $_final );
				$_final = str_replace( "]", "&#93;"  , $_final );
				
				$txt	= str_replace( $replacement['find'], $_final, $txt );
			}
		}
		
		$this->noParseStorage	= array();

		return $txt;
	}
	
	/**
	 * Callback to auto-parse urls
	 *
	 * @access	private
	 * @param	array		Matches from the regular expression
	 * @return	string		Converted text
	 */
	private function _autoParseUrls( $matches )
	{
		$_extra		= '';

		/* Basic checking */
		if ( stristr( $matches[1], 'href' ) )
		{
			return $matches[0];
		}
		
		if ( isset( $matches[4] ) AND stristr( $matches[4], '</a>' ) )
		{
			return $matches[0];
		}
		
		/* Check for XSS */
		if ( ! IPSText::xssCheckUrl( $matches[2] ) )
		{
			return $matches[0];
		}
		
		if( substr( $matches[2], -1 ) == ',' )
		{
			$matches[2]	= rtrim( $matches[2], ',' );
			$_extra		= ',';
		}
		
		/* Check for ! which is &#xx; at this point */
		if( preg_match( "/&#\d+?;$/", $matches[2], $_m ) )
		{
			$matches[2]	= str_replace( $_m[0], '', $matches[2] );
			$_extra		= $_m[0];
		}
		
		if( $this->settings['bbcode_automatic_media'] )
		{
			$media		= $this->cache->getCache( 'mediatag' );
	
	       	if( is_array($media) AND count($media) )
			{
				foreach( $media as $type => $r )
				{
					if( preg_match( "#^" . $r['match'] . "$#is", $matches[2] ) )
					{
						//-----------------------------------------
						// Do it this way so we can capture disable_flash
						//-----------------------------------------
						
						$this->cache->updateCacheWithoutSaving( '_tmp_autoparse_media', 1 );
						$_result	= $this->parseBbcode( $matches[1] . '[media]' . $matches[2] . '[/media]' . $_extra, 'display', 'media' );
						$this->cache->updateCacheWithoutSaving( '_tmp_autoparse_media', 0 );
						
						return $_result;
					}
				}
			}
		}
		
		/* Ensure bbcode is stripped for the actual URL */
		/* http://community.invisionpower.com/tracker/issue-22580-bbcode-breaks-link-add-bold-formatting-to-part-of-link/ */
		if ( preg_match( "#\[\w#", $matches[2] ) )
		{
			$wFormatting = $matches[2];
			$matches[2]  = $this->stripAllTags( $matches[2] );
			
			return $this->parseBbcode( $matches[1] . '[url="' . $matches[2] . '"]' . $wFormatting . '[/url]' . $_extra, 'display', 'url' );
		}
		else
		{
			return $this->parseBbcode( $matches[1] . '[url]' . $matches[2] . '[/url]' . $_extra, 'display', 'url' );
		}
	}
	
	/**
	 * Reset function to call to trigger "new post" for non-parsed content
	 *
	 * @access	public
	 * @return	void
	 */
	public function resetPerPost()
	{
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_images', 0 );
	}
}