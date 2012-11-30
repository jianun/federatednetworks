<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Metaweblog API Functions
 * Last Updated: $Date: 2010-07-07 05:53:45 -0400 (Wed, 07 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Blog
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 6607 $
 *
 */

class xmlrpc_server
{
   /**
	* Defines the service for WSDL
	*
	* @access	private
	* @var		array
	*/
	public $__dispatch_map = array();

	/**
	 * IPS API SERVER Class
	 *
	 * @access	public
	 * @var		object
	 */
	public $classApiServer;

	/**
	 * Error string
	 *
	 * @access	public
	 * @var		string
	 */
	public $error = "";
	
   /**
	* Global registry
	*
	* @access 	private
	* @var 		object
	*/
	private $registry;

	/**
	 * CONSTRUCTOR
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry )
    {
		/* Make registry objects */
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();

		/* Load the Blog functions library */
		$registry->DB()->loadCacheFile( BLOG_PATH . 'sql/' . ips_DBRegistry::getDriverType() . '_blog_queries.php', 'sql_blog_queries' );
		require_once( IPS_ROOT_PATH . 'applications_addon/ips/blog/sources/classes/blogFunctions.php' );
		$registry->setClass( 'blogFunctions', new blogFunctions( $registry ) );
		
		/* Language */
		$this->registry->class_localization->loadLanguageFile( array( 'public_portal' ), 'blog' );
		
		/* Allowed Methods and Dispatch Map */
		require_once( DOC_IPS_ROOT_PATH . 'interface/blog/apis/methods_metaweblog.php' );

		if ( is_array( $_METAWEBLOG_ALLOWED_METHODS ) and count( $_METAWEBLOG_ALLOWED_METHODS ) )
		{
			foreach( $_METAWEBLOG_ALLOWED_METHODS as $_method => $_data )
			{
				$this->__dispatch_map[ $_method ] = $_data;
			}
		}
	}

	/**
	 * XMLRPC_server::getCategories()
	 *
	 * Returns a list of the Blogs categories.
	 *
	 * @access	public
	 * @param	string	$blogid			Application key
	 * @param	string  $username       Username
	 * @param	string  $password		Password
	 * @return	string	xml document
	 **/
	public function getCategories( $blogid, $username, $password )
	{
		/* Authenticate the request */
		if ( $this->_authenticate( $username, $password ) )
		{
			if ( $this->blog['blog_id'] != $blogid )
			{
				$this->classApiServer->apiSendError( 100, $this->registry->class_localization->words['blogger_error_3'] );
				exit();
			}
		
			/* Query tags */
			$this->DB->build( array( 
										'select'	=> '*', 
										'from'		=> 'tags_index', 
										'where'		=> "app='blog' AND type='category' AND type_id_2={$blogid}" 
							)	);
			$this->DB->execute();
			
			/* Loop through tags and build array */
			$return_array = array();
			
			while( $r = $this->DB->fetch() )
			{
				$return_array[] = array(
										'description'	=> $r['tag'],
										'htmlUrl'		=> "{$this->settings['base_url']}app=blog&module=display&section=blog&blogid={$this->blog['blog_id']}&tag={$r['tag']}",
										'rssUrl'		=> ''
									);
			}
			
			/* Send response */
			$this->classApiServer->apiSendReply( $return_array );
			exit();
		}
		else
		{
			$this->classApiServer->apiSendError( 100, $this->error );
			exit();
		}
	}

	/**
	 * XMLRPC_server::getRecentPosts()
	 *
	 * Returns a list of the Blogs categories.
	 *
	 * @access	public
	 * @param	int		$blogid       	   			Blog ID
	 * @param	string	$username					Username
	 * @param	string	$password					Password
	 * @param	int		$numberOfPosts				Number of posts
	 * @return	string	xml
	 **/
	public function getRecentPosts( $blogid, $username, $password, $numberOfPosts )
	{
		/* INIT */
		$return_array = array();

		/* Authenticate */
		if( $this->_authenticate( $username, $password ) )
		{
			/* Check the blog id */
			if ( $this->blog['blog_id'] != $blogid )
			{
				$this->classApiServer->apiSendError( 100, $this->registry->class_localization->words['blogger_error_3'] );
				exit();
			}
			
			/* Blog URL */
			$blog_url = str_replace( "&amp;", "&", $this->registry->blogFunctions->getBlogUrl( $this->blog['blog_id'] ) );

			$this->DB->build( array( 
										'select'	=> 'e.*',
										'from'	 	=> array( 'blog_entries' => 'e' ),
										'where'		=> "e.blog_id={$this->blog['blog_id']}",
										'order'		=> 'e.entry_date DESC',
										'limit'		=> array( 0, intval( $numberOfPosts ) ),
										'add_join'	=> array(
										 					array(
										 							'select'	=> 'm.member_group_id, m.mgroup_others',
													 				'from'		=> array( 'members' => 'm' ),
													 				'where'		=> 'm.member_id=e.entry_author_id',
																	'type'		=> 'left'
									 							)
										 					)
							)	);
			$q = $this->DB->execute();
			
			while( $entry = $this->DB->fetch( $q ) )
			{
				/* Setup Parser */
				IPSText::getTextClass('parser')->parse_html				= $entry['entry_html_state'] ? 1 : 0;
				IPSText::getTextClass('parser')->parse_wordwrap			= $this->settings['post_wordwrap'];
				IPSText::getTextClass('parser')->parse_nl2br			= $entry['entry_html_state'] == 2 ? 1 : 0;
				IPSText::getTextClass('parser')->parse_smilies			= $entry['entry_use_emo'] ? 1: 0;
				IPSText::getTextClass('parser')->parsing_section		= 'blog_entry';
				IPSText::getTextClass('parser')->parsing_mgroup			= $entry['member_group_id'];
				IPSText::getTextClass('parser')->parsing_mgroup_others	= $entry['member_group_id'];
				
				/* Parse */
				$entry['entry'] = IPSText::getTextClass('parser')->preDisplayParse( $entry['entry'] );
				
				/* Tags */
				$this->DB->build( array( 'select' => 'tag', 'from' => 'tags_index', 'where' => "app='blog' AND type_id={$entry['entry_id']} AND type_id_2={$entry['blog_id']}" ) );
				$this->DB->execute();

				$tags = array();
				while( $r = $this->DB->fetch() )
				{
					$tags[] = $r['tag'];
				}
				
				/* Add to output */
				$return_array[] = array( 
											'categories'	=> $tags,
											'dateCreated'	=> gmdate("Ymd\TH:i:s",$entry['entry_date']),
											'description'	=> $entry['entry'],
											'link'			=> $blog_url.'showentry='.$entry['entry_id'],
											'postid'		=> $entry['entry_id'],
											'title'			=> $entry['entry_name']
										);
			}
			$force_array = array( 'dateCreated'		=> 'dateTime.iso8601' );

			$this->classApiServer->apiSendReply( $return_array, 0, $force_array );
			exit();
		}
		else
		{
			$this->classApiServer->apiSendError( 100, $this->error );
			exit();
		}
	}

	/**
	 * XMLRPC_server::newPost()
	 *
	 * Post a new entry.
	 *
	 * @access	public
	 * @param	int		$blogid       	   			Blog ID
	 * @param	string	$username					Username
	 * @param	string	$password					Password
	 * @param	array	$content					Post content
	 * @param	bool	$publish					Publish?
	 * @return	string	xml
	 **/
	public function newPost( $blogid, $username, $password, $content, $publish )
	{		
		/* Authenticate */
		if ( $this->_authenticate( $username, $password ) )
		{
			/* Check the ID */
			if ( $this->blog['blog_id'] != $blogid )
			{
				$this->classApiServer->apiSendError( 100, $this->registry->class_localization->words['blogger_error_3'] );
				exit();
			}
			
			/* External blog? */
			if ( $this->blog['blog_type'] != 'local' )
			{
				$this->classApiServer->apiSendError( 100, 'Cannot post to external blog' );
				exit();
			}
			
			/* Compile the post */
			if ( ! $this->_compilePost( $content, $publish ) )
			{
				$this->classApiServer->apiSendError( 100, $this->error );
				exit();
			}
			
			/* Insert the blog */
			$this->DB->insert( 'blog_entries', $this->entry );
			$entry_id = $this->DB->getInsertId();
			
			/* Rebuild teh blog stats */
			$this->registry->blogFunctions->rebuildBlog( $this->blog['blog_id'] );

			/* If the entry is published, do tracker and pings */
			if( $publish )
			{
				$this->blogTracker( $entry_id, $this->entry['entry_name'], $this->entry['entry'] );
				$this->blogPings ( $entry_id, $this->entry );
			}
            
			/* Update Blog Stats */
			$r = $this->registry->cache()->getCache( 'blog_stats' );
            
			$r['blog_stats']['stats_num_entries']++;
			$this->registry->cache()->setCache( 'blog_stats', $r, array( 'array' => 1, 'donow' => 1 ) );

			$this->classApiServer->apiSendReply( $entry_id );
			exit();
		}
		else
		{
			$this->classApiServer->apiSendError( 100, $this->error );
			exit();
		}
	}

	/**
	 * XMLRPC_server::editPost()
	 *
	 * Edit the entry.
	 *
	 * @access	public
	 * @param	int		$postid       	   			Entry ID
	 * @param	string	$username					Username
	 * @param	string	$password					Password
	 * @param	string	$content					Post content
	 * @param	bool	$publish					Publish?
	 * @return	string	xml
	 **/
	public function editPost( $postid, $username, $password, $content, $publish )
	{
		/* Authenticate request */
		if( $this->_authenticate( $username, $password ) )
		{
			/* Query the entry */
			$entry = $this->DB->buildAndFetch( array( 
													'select'	=>	'*',
													'from'		=>	'blog_entries',
													'where'		=>	'entry_id = ' . intval( $postid )
										 	)		 );
												
			/* Make sure we have an entry */
			if( ! $entry['entry_id'] )
			{
				$this->classApiServer->apiSendError( 100, sprintf( $this->registry->class_localization->words['blogger_error_2'], intval( $postid ) ) );
				exit();
			}
			
			/* Compile the post */
			if ( ! $this->_compilePost( $content, $publish ) )
			{
				$this->classApiServer->apiSendError( 100, $this->error );
				exit();
			}
			
			/* Setup the new entry data */
			$this->entry['blog_id']				= $entry['blog_id'];
			$this->entry['entry_author_id']		= $entry['entry_author_id'];
			$this->entry['entry_author_name']	= $entry['entry_author_name'];
			$this->entry['entry_date']			= $entry['entry_date'];
			$this->entry['entry_last_update']	= $entry['entry_last_update'];
			$this->entry['entry_edit_time']		= time();
			$this->entry['entry_edit_name'] 	= $this->memberData['members_display_name'];
			$this->entry['entry_poll_state']	= $entry['entry_poll_state'];

			/* Update the entry */
			$this->DB->update( 'blog_entries', $this->entry, 'entry_id='.$entry['entry_id'] );

			/* Rebuild the blog */
			$this->registry->blogFunctions->rebuildBlog ( $this->blog['blog_id'] );

			/* Send Response */
			$this->classApiServer->apiSendReply();
			exit();
		}
		else
		{
			$this->classApiServer->apiSendError( 100, $this->error );
			exit();
		}
	}

	/**
	 * XMLRPC_server::getPost()
	 *
	 * Get an entry.
	 *
	 * @access	public
	 * @param	int		$postid       	   			Entry ID
	 * @param	string	$username					Username
	 * @param	string	$password					Password
	 * @return	string	xml
	 **/
	public function getPost( $postid, $username, $password )
	{
		/* Authenticate the request */
		if( $this->_authenticate( $username, $password ) )
		{
			/* Query the blog entry */
			$entry = $this->DB->buildAndFetch( array(
														'select'	=>	'e.*',
														'from'		=>	array( 'blog_entries' => 'e' ),
														'where'		=>	'e.entry_id = ' . intval( $postid ),
														'add_join'	=> array(
														 					array(
																					'select'	=> 'm.member_group_id, m.mgroup_others',
																					'from'		=> array( 'members' => 'm' ),
																					'where'		=> 'm.member_id=e.entry_author_id',
																					'type'		=> 'left'
																				)
														 					)
											)	 );
			
			/* Make sure we found an entry */
			if( ! $entry['entry_id'] )
			{
				$this->classApiServer->apiSendError( 100, sprintf( $this->registry->class_localization->words['blogger_error_2'], intval($postid) ) );
				exit();
			}
			
			/* Tags */
			$this->DB->build( array( 'select' => 'tag', 'from' => 'tags_index', 'where' => "app='blog' AND type_id={$entry['entry_id']} AND type_id_2={$entry['blog_id']}" ) );
			$this->DB->execute();

			$tags = array();
			while( $r = $this->DB->fetch() )
			{
				$tags[] = $r['tag'];
			}

			/* Setup the parser */
			IPSText::getTextClass('parser')->parse_html				= $entry['entry_html_state'] ? 1 : 0;
			IPSText::getTextClass('parser')->parse_wordwrap			= $this->settings['post_wordwrap'];
			IPSText::getTextClass('parser')->parse_nl2br			= $entry['entry_html_state'] == 2 ? 1 : 0;
			IPSText::getTextClass('parser')->parse_smilies			= $entry['entry_use_emo'] ? 1: 0;
			IPSText::getTextClass('parser')->parsing_section		= 'blog_entry';
			IPSText::getTextClass('parser')->parsing_mgroup			= $entry['member_group_id'];
			IPSText::getTextClass('parser')->parsing_mgroup_others	= $entry['member_group_id'];
			
			/* Parse the entry */
			$entry['entry'] = IPSText::getTextClass('parser')->preDisplayParse( $entry['entry'] );

			/* Build the return array */
			$return_array = array(
									'postid'		=> $entry['entry_id'],
									'dateCreated'	=> gmdate("Ymd\TH:i:s",$entry['entry_date']),
									'title'			=> $entry['entry_name'],
									'description'	=> $entry['entry'],
									'categories'	=> $tags
								);
								
			$force_array = array( 'dateCreated'		=> 'dateTime.iso8601' );
				
			/* Send Post */
			$this->classApiServer->apiSendReply( $return_array, 0, $force_array );
			exit();
		}
		else
		{
			$this->classApiServer->apiSendError( 100, $this->error );
			exit();
		}
	}

	/**
	 * XMLRPC_server::newMediaObject()
	 *
	 * Post a new entry.
	 *
	 * @access	public
	 * @param  int		$blogid       	   			Blog ID
	 * @param  string	$username					Username
	 * @param  string	$password					Password
	 * @param  array	$file						Uploaded file details
	 * @return string	xml
	 **/
	public function newMediaObject( $blogid, $username, $password, $file )
	{
		/* We don't support this yet, just send a response */
		$this->classApiServer->apiSendReply();
	}

	/**
	 * _authenticate()
	 *
	 * Authenticates the username and password
	 *
	 * This will return
	 * - false	(Failed)
	 * - true	(Succes)
	 *
	 * @access	private
	 * @param  string  $username       	   			Username
	 * @param  string  $password					Password
	 * @return boolean
	 **/
	private function _authenticate( $username, $password )
	{
		//-----------------------------------------
		// Are they banned?
		//-----------------------------------------
		if ( is_array( $this->caches['banfilters'] ) and count( $this->caches['banfilters'] ) )
		{
			foreach ($this->caches['banfilters'] as $ip)
			{
				$ip = str_replace( '\*', '.*', preg_quote($ip, "/") );

				if ( preg_match( "/^$ip$/", $this->member->ip_address ) )
				{
					$this->error = $this->registry->class_localization->words['blogger_banned_msg'];
					return false;
				}
			}
		}

		//-----------------------------------------
		// load the member
		//-----------------------------------------
		$member = IPSMember::load( IPSText::parseCleanValue( $username ), 'extendedProfile', 'username' );

		if ( ! $member['member_id'] )
		{
			$this->error = $this->registry->class_localization->words['blogger_unknown_user'];
			return false;
		}
		
		ips_MemberRegistry::setMember( $member['member_id'] );

		//--------------------------------
		//  Is the board offline?
		//--------------------------------

		if ($this->settings['board_offline'] == 1)
		{
			if ($member['g_access_offline'] != 1)
			{
				$this->error = $this->registry->class_localization->words['blogger_board_offline'];
				return false;
			}
		}

		//-----------------------------------------
		// Temporarely banned?
		//-----------------------------------------
		if ( $member['temp_ban'] )
		{
			$this->error = $this->registry->class_localization->words['blogger_suspended'];
			return false;
		}

		//-----------------------------------------
		// Load the Blog
		//-----------------------------------------
		$this->registry->blogFunctions->buildPerms();
		
		//-----------------------------------------
		// Users can have more than one blog - just
		// grab first one mysql returns
		//-----------------------------------------
		
		$blog = $this->DB->buildAndFetch( array( 
												'select'	=> 'blog_id, blog_name',
												'from'		=> 'blog_blogs',
												'where'		=> "member_id={$member['member_id']}" 
									)	);

		if ( ! $blog['blog_id'] )
		{
			$this->error = $this->registry->class_localization->words['blogger_noblog'];
			return false;
		}

		if ( ! $this->blog = $this->registry->blogFunctions->loadBlog( $blog['blog_id'], 1 ) )
		{
			$this->error = $this->blog_std->error;
			return false;
		}

		//-----------------------------------------
		// Blog post permissions?
		//-----------------------------------------
		if ( !$this->blog['allow_entry'] )
		{
			$this->error = $this->registry->class_localization->words['blogger_nopost'];
			return false;
		}

		//-----------------------------------------
		// Validate password?
		//-----------------------------------------
		if ( !$this->settings['blog_allow_xmlrpc'] or !$this->blog['blog_settings']['enable_xmlrpc'] )
		{
			$this->error = $this->registry->class_localization->words['blogger_noxmlrpc'];
			return false;
		}

		if ( $this->blog['blog_settings']['xmlrpc_password'] != md5( IPSText::parseCleanValue( $password ) ) )
		{
			if ( isset( $this->blog['blog_settings']['xmlrpc_failedattempts'] ) && $this->blog['blog_settings']['xmlrpc_failedattempts'] > 5 )
			{
				$this->blog['blog_settings']['enable_xmlrpc'] = 0;
				$this->blog['blog_settings']['xmlrpc_failedattempts'] = 0;
				$blog_settings = serialize ( $this->blog['blog_settings'] );
				$this->DB->update( 'blog_blogs', array( 'blog_settings' => $blog_settings ), "blog_id = {$this->blog['blog_id']}" );
			}
			else
			{
				$this->blog['blog_settings']['xmlrpc_failedattempts'] = isset( $this->blog['blog_settings']['xmlrpc_failedattempts'] ) ? intval($this->blog['blog_settings']['xmlrpc_failedattempts'])+1 : 1;
				$blog_settings = serialize ( $this->blog['blog_settings'] );
				$this->DB->update( 'blog_blogs', array( 'blog_settings' => $blog_settings ), "blog_id = {$this->blog['blog_id']}" );
			}
			$this->error = $this->registry->class_localization->words['blogger_inv_pass'];
			return false;
		}
		else
		{
			if ( isset( $this->blog['blog_settings']['xmlrpc_failedattempts'] ) && $this->blog['blog_settings']['xmlrpc_failedattempts'] > 0 )
			{
				$this->blog['blog_settings']['xmlrpc_failedattempts'] = 0;
				$blog_settings = serialize ( $this->blog['blog_settings'] );
				$this->DB->update( 'blog_blogs', array( 'blog_settings' => $blog_settings ), "blog_id = {$this->blog['blog_id']}" );
			}
		}
		
		//-----------------------------------------
		// Set the member data
		//-----------------------------------------
		
		$this->memberData	= $member;

		return true;
	}

	/**
	 * _compilePost()
	 *
	 * Compiles a blog entry for inserting to the database
	 *
	 * @access	private
	 * @param	array 	$content
	 * @param	bool	$password
	 * @return	bool
	 **/
	private function _compilePost( $content, $publish )
	{
		/* Set a default max post length, if we don't have one */
		$this->settings['blog_max_entry_length'] = $this->settings['blog_max_entry_length'] ? $this->settings['blog_max_entry_length'] : 2140000;

		/* Check to make sure we have text for this entry */
		if( strlen( trim( IPSText::br2nl( $content['description'] ) ) ) < 1 )
		{
			$this->error = $this->registry->class_localization->words['blogger_emptypost'];
			return false;
		}
		
		/* Check to make sure that the entry is not too long */
		if( strlen( $content['description'] ) > ( $this->settings['blog_max_entry_length'] * 1024 ) )
		{
			$this->error = $this->registry->class_localization->words['blogger_toolong'];
			return false;
		}
		
		/* Check the title */
		$title = $this->pfCleanTopicTitle( $content['title'] );
		$title = IPSText::getTextClass( 'bbcode' )->stripBadWords( $title );

		if( ! $title )
		{
			$this->error = $this->registry->class_localization->words['blogger_notitle'];
			return false;
		}

		/* Setup the parser */
		IPSText::getTextClass('parser')->parse_smilies		= 1;
		IPSText::getTextClass('parser')->parse_html			= 0;
		IPSText::getTextClass('parser')->parse_bbcode		= 1;
		IPSText::getTextClass('parser')->parsing_section	= 'blog_entry';
		
		/* Cleanup and parse the entry */
		$description = preg_replace( "#<br />(\r)?\n#is", "<br />", $content['description'] );
		
		/* Time */
		$dte = time();
		
		/* Set the entry */
		$this->entry = array(
						'blog_id'			  => $this->blog['blog_id'],
						'entry_author_id'	  => $this->memberData['member_id'],
						'entry_author_name'	  => $this->memberData['members_display_name'],
						'entry_date'		  => $dte,
						'entry_name'		  => $title,
						'entry'     		  => IPSText::getTextClass('bbcode')->preDbParse( $description ),
						'entry_status'		  => ($publish ? 'published' : 'draft'),
						'entry_post_key'	  => md5(microtime()),
						'entry_html_state'	  => 0,
						'entry_use_emo'		  => 1,
						'entry_last_update'	  => $dte,
						'entry_gallery_album' => 0,
						'entry_poll_state'    => 0,
					 );
		
		/* Check for parser errors */
		$testParse = IPSText::getTextClass('parser')->preDisplayParse( $description );			

		/* Set any parser errors */
		if( is_array( IPSText::getTextClass('parser')->error ) && count( IPSText::getTextClass('parser')->error ) > 0 )
		{
	    	$this->error = implode( " : ", IPSText::getTextClass('parser')->error );
	    	return false;
	    }

		return true;
	}
	
	/**
	 * Clean topic title
	 *
	 * @param  string  $title
	 * @return string
	 **/
	public function pfCleanTopicTitle($title="")
	{
		if( $this->settings['etfilter_shout'] )
		{
			if( function_exists('mb_convert_case') )
			{
				if( in_array( strtolower( $this->settings['gb_char_set'] ), array_map( 'strtolower', mb_list_encodings() ) ) )
				{
					$title = mb_convert_case( $title, MB_CASE_TITLE, $this->settings['gb_char_set'] );
				}
				else
				{
					$title = ucwords( strtolower($title) );
				}
			}
			else
			{
				$title = ucwords( strtolower($title) );
			}
		}
		
		$title = IPSText::parseCleanValue( $title );
		
		if( $this->settings['etfilter_punct'] )
		{
			$title	= preg_replace( "/\?{1,}/"      , "?"    , $title );		
			$title	= preg_replace( "/(&#33;){1,}/" , "&#33;", $title );
		}

		//-----------------------------------------
		// The DB column is 250 chars, so we need to do true mb_strcut, then fix broken HTML entities
		// This should be fine, as DB would do it regardless (cept we can fix the entities)
		//-----------------------------------------

		$title = preg_replace( "/&(#{0,}([a-zA-Z0-9]+?)?)?$/", '', IPSText::mbsubstr( $title, 0, 250 ) );
		
		$title = IPSText::stripAttachTag( $title );
		$title = str_replace( "<br />", "", $title  );
		$title = trim( $title );
		
		//$title = IPSText::getTextClass( 'bbcode' )->stripBadWords( $title );

		return $title;
	}
	
	/**
	 * Blog tracker
	 *
	 * @param  integer  $entry_id
	 * @param  string   $entry_name
	 * @param  string   $entry
	 * @return bool
	 **/
	public function blogTracker( $entry_id="", $entry_name="", $entry="" )
	{
		if( $entry_id == "" )
		{
			return TRUE;
		}

		//-----------------------------------------
		// We will send these out immediatly (if published)
		//-----------------------------------------

		$count = 0;
		$gotem = array();

		$this->DB->build( array( 
								'select'   => 'bt.tracker_id',
								'from'     => array( 'blog_tracker' => 'bt' ),
								'add_join' => array(
													array( 
															'select' => 'm.name, m.email, m.member_id, m.language, m.last_activity, m.members_display_name, m.member_group_id, m.mgroup_others',
															'from'   => array( 'members' => 'm' ),
															'where'  => "bt.member_id=m.member_id AND m.member_id <> {$this->memberData['member_id']}",
															'type'   => 'left' 
														),
													array( 
															'select' => 'p.perm_type_id as auth_blog_id',
															'from'   => array( 'permission_index' => 'p' ),
															'where'  => "p.perm_type='blog' AND p.perm_type_id=bt.blog_id AND p.authorized_users LIKE '%,{$this->memberData['member_id']},%'",
															'type'   => 'left' 
														)  
												),
								'where'    => 'bt.blog_id=' . intval( $this->blog['blog_id'] ) . ' AND m.member_id IS NOT NULL'
						)	);

		$this->DB->execute();

		while( $r = $this->DB->fetch() )
		{
			$gotem[ $r['member_id'] ] = $r;
		}

		//-----------------------------------------
		// Row, row and parse, parse
		//-----------------------------------------

		if( count( $gotem ) )
		{
			/* Load teh blog templates */
			$this->lang->loadLanguageFile( array( 'public_emails' ), 'blog' );
			
			foreach( $gotem as $r )
			{
				if( $this->blog['blog_view_level'] == 'private' || $this->blog['blog_settings']['category_cache'][$this->entry['category_id']]['category_type'] != 'public' )
				{
					//-----------------------------------------
					// Mod?
					//-----------------------------------------
					$mod_canviewprivate = 0;
					if( count( $this->caches['blogmods'] ) )
					{
						$mgroups = explode( ',', $r['mgroup_others'] );
						foreach( $this->caches['blogmods'] as $i => $m )
						{
							if( ( $m['moderate_mg_id'] == $r['member_id'] && $m['moderate_type'] == 'member' ) or
								 ( $m['moderate_mg_id'] == $r['member_group_id'] && $m['moderate_type'] == 'group' ) or
								 ( in_array($m['moderate_mg_id'], $mgroups) && $m['moderate_type'] == 'group' ) )
							{
								$mod_canviewprivate = $m['moderate_can_view_private'];
							}
						}
					}

					if( $this->blog['blog_view_level'] == 'private' && $r['member_id'] != $this->blog['member_id'] && $this->blog['blog_id'] != $r['auth_blog_id'] && ! $mod_canviewprivate )
					{
						continue;
					}
					
					if( $this->blog['blog_view_level'] == 'privateclub' && $r['member_id'] != $this->blog['member_id'] && $this->blog['blog_id'] != $r['auth_blog_id'] && ! $mod_canviewprivate )
					{
						continue;
					}
				}

				$r['language'] = $r['language'] ? $r['language'] : 'en';
				
				IPSText::getTextClass('email')->getTemplate( 'subs_new_entry' );
					
				IPSText::getTextClass('email')->buildMessage( array(
																	'BLOG_ID'         => $this->blog['blog_id'],
																	'BLOG_NAME'		  => $this->blog['blog_name'],
																	'ENTRY_ID'        => $entry_id,
																	'ENTRY_NAME'      => $entry_name,
																	'NAME'            => $r['members_display_name'],
																	'POSTER'          => $this->memberData['members_display_name'],
																	'ENTRY'           => $entry,
															)	);

				//-----------------------------------------
				// If it is published, mail now, else put it in the tracker queue
				//-----------------------------------------
				if( $this->entry['entry_status'] == 'published' )
				{
					$this->DB->insert( 'mail_queue', array( 'mail_to' => $r['email'], 'mail_from' => '', 'mail_date' => time(), 'mail_subject' => $this->lang->words['bt_subject'], 'mail_content' => IPSText::getTextClass('email')->message ) );
					$count++;
				}
				else
				{
					$this->DB->insert( 'blog_tracker_queue', array( 'blog_id' => $this->blog['blog_id'], 'entry_id' => $entry_id, 'tq_to' => $r['email'], 'tq_subject' => $this->lang->words['bt_subject'], 'tq_content' => IPSText::getTextClass('email')->message ) );
				}
			}
		}

		if( $count > 0 )
		{
			//-----------------------------------------
			// Update cache with remaning email count
			//-----------------------------------------
			$cache = $this->caches['systemvars'];
			$cache['mail_queue'] += $count;
			$this->cache->setCache( 'systemvars', $cache, array( 'array' => 1 ) );
		}
		
		return TRUE;
	}
	
	/**
	 * Ping-e-dee-doo
	 *
	 * @param  integer  $entry_id
	 * @param  array    $entry
	 * @return void
	 **/
	public function blogPings( $entry_id, $entry )
	{
		if ( $this->settings['blog_allow_pingblogs'] && is_array( $this->blog['blog_settings']['pings'] ) && count( $this->blog['blog_settings']['pings'] ) > 0 )
		{
			$ping_added = 0;
			foreach( $this->blog['blog_settings']['pings'] as $service => $enabled )
			{
				if( $enabled )
				{
					$updateping['ping_active'] = ( $entry['entry_status']=='published' ? 1 : 0 );
					$updateping['ping_time'] = time();
					$updateping['blog_id'] = $this->blog['blog_id'];
					$updateping['entry_id'] = $entry_id;
					$updateping['ping_service'] = $service;
					$this->DB->insert( 'blog_updatepings', $updateping );
					$ping_added = $updateping['ping_active'] ? 1 : 0;
				}
			}
			
			if( $ping_added )
			{
				// activate task
				require_once( IPS_ROOT_PATH . 'sources/classes/class_taskmanager.php' );
				$task = new class_taskmanager( $this->registry );

				$this_task = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'task_manager', 'where' => "task_key='blogpings'" ) );
				$newdate = $task->generateNextRun($this_task);
				$this->DB->update( 'task_manager', array( 'task_next_run' => $newdate, 'task_enabled' => 1 ), "task_id=".$this_task['task_id'] );
				$task->saveNextRunStamp();
			}
		}
	}
	

}