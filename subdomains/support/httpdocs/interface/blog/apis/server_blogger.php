<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Blogger API Functions
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Blog
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 5713 $
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
	 **/
	public function __construct( ipsRegistry $registry )
    {
		//-----------------------------------------
		// Set IPS CLASS
		//-----------------------------------------

		$this->registry = $registry;
		$this->request	= $this->registry->fetchRequest();

		/* Load the Blog functions library */
		require_once( IPS_ROOT_PATH . 'applications_addon/ips/blog/sources/classes/blogFunctions.php' );
		$registry->setClass( 'blogFunctions', new blogFunctions( $registry ) );

   		//-----------------------------------------
    	// Load allowed methods and build dispatch
		// list
    	//-----------------------------------------

		require_once( DOC_IPS_ROOT_PATH . 'interface/blog/apis/methods_blogger.php' );

		if ( is_array( $_METAWEBLOG_ALLOWED_METHODS ) and count( $_METAWEBLOG_ALLOWED_METHODS ) )
		{
			foreach( $_METAWEBLOG_ALLOWED_METHODS as $_method => $_data )
			{
				$this->__dispatch_map[ $_method ] = $_data;
			}
		}
	}

	/**
	 * XMLRPC_server::getUsersBlogs()
	 *
	 * Retrieves a user's blog entries
	 *
	 * This will return a param "response" with either
	 * - FAILED    		 (Unknown failure)
	 * - SUCCESS    	 (Added OK)
	 *
	 *
	 * @access	public
	 * @param	string	$appkey			Application key
	 * @param	string  $username       Username
	 * @param	string  $password		Password
	 * @return	string	xml document
	 **/
	public function getUsersBlogs( $appkey, $username, $password )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$return     = 'FAILED';

		//-----------------------------------------
		// Authenticate
		//-----------------------------------------

		if ( $this->_authenticate( $username, $password ) )
		{
			//-----------------------------------------
			// return
			//-----------------------------------------

			$return = 'SUCCESS';
			$blog_url = substr( str_replace( "&amp;", "&", $this->registry->blogFunctions->getBlogUrl( $this->blog['blog_id'] ) ), 0, -1 );
			$this->classApiServer->apiSendReply( array( array( 'url'		=> $blog_url,
			 													   'blogid'		=> $this->blog['blog_id'],
			 													   'blogName'	=> IPSText::UNhtmlspecialchars( $this->blog['blog_name'] )
			 										)	  )		 );
			exit();
		}
		else
		{
			$this->classApiServer->apiSendError( 100, $this->error );
			exit();
		}
	}

	/**
	 * XMLRPC_server::getUserInfo()
	 *
	 * Retrieve user info
	 *
	 * This will return a param "response" with either
	 * - FAILED    		 (Unknown failure)
	 * - SUCCESS    	 (Added OK)
	 *
	 *
	 * @access	public
	 * @param	string  $appkey       	   			Appkey (ignored)
	 * @param	string  $username       	   			Username
	 * @param	string  $password					Password
	 * @return	string	xml
	 **/
	public function getUserInfo( $appkey, $username, $password )
	{
		//-----------------------------------------
		// Authenticate
		//-----------------------------------------

		if ( $this->_authenticate( $username, $password ) )
		{
			//-----------------------------------------
			// return
			//-----------------------------------------
			$this->classApiServer->apiSendReply( array( 'nickname'	=> $this->memberData['members_display_name'],
															'userid'	=> $this->memberData['member_id'],
			 												'url'		=> ipsRegistry::$settings['board_url'].'/index.'.ipsRegistry::$settings['php_ext'].'?showuser='.$this->memberData['member_id'],
															'email'		=> $this->memberData['email']
			 										)	  );
			exit();
		}
		else
		{
			$this->classApiServer->apiSendError( 100, $this->error );
			exit();
		}
	}

	/**
	 * XMLRPC_server::deletePost()
	 *
	 * Deletes a Blog entry.
	 *
	 * @access	public
	 * @param	string	$appkey			Appkey (ignored)
	 * @param	int		$postid			Entry ID
	 * @param	string	$username		Username
	 * @param	string	$password		Password
	 * @param	bool	$publish		Publish (ignored)
	 * @return	string	xml
	 **/
	public function deletePost( $appkey, $postid, $username, $password, $publish )
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_portal' ), 'blog' );

		//-----------------------------------------
		// Authenticate
		//-----------------------------------------

		if ( $this->_authenticate( $username, $password ) )
		{
			if (!$this->registry->blogFunctions->allowDelEntry( $this->blog ) )
			{
				$this->classApiServer->apiSendError( 100, $this->registry->class_localization->words['blogger_error_1'] );
				exit();
			}

			//-----------------------------------------
			// find the entry
			//-----------------------------------------
			$eid = intval($postid);
		    $entry = $this->registry->DB()->buildAndFetch( array ( 'select'	=>	'*',
														   'from'	=>	'blog_entries',
														   'where'	=>	"entry_id = ".$eid
												 )		 );
			if ( ! $entry['entry_id'] )
			{
				$this->classApiServer->apiSendError( 100, sprintf( $this->registry->class_localization->words['blogger_error_2'], $eid ) );
				exit();
			}

			//-----------------------------------------
			// delete the entry
			//-----------------------------------------
			
			$this->registry->blogFunctions->deleteEntries( array( $eid ) );

			$this->addModlog( $this->registry->class_localization->words['blogger_blog_prefix'] . "({$this->blog['blog_id']}) '{$this->blog['blog_name']}': {$this->registry->class_localization->words['blogger_deleted_log']} '{$entry['entry_name']}'" );

			//-------------------------------------------------
			// Return
			//-------------------------------------------------
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
	 * XMLRPC_server::newPost()
	 *
	 * Adds a new post to the Blog.
	 *
	 * @access	public
	 * @param	string  $appkey		Appkey (ignored)
	 * @param	int     $blogid		Blog ID
	 * @param	string  $username	Username
	 * @param	string  $password	Password
	 * @param	string  $contentd	Content
	 * @param	bool    $publish	Publish (ignored)
	 * @return	string	xml
	 **/
	public function newPost( $appkey, $blogid, $username, $password, $content, $publish )
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_portal' ), 'blog' );
		
		//-----------------------------------------
		// Authenticate
		//-----------------------------------------

		if ( $this->_authenticate( $username, $password ) )
		{
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

			//-----------------------------------------
			// return
			//-----------------------------------------
			if ( ! $this->_compilePost( $content['description'], $publish ) )
			{
				$this->classApiServer->apiSendError( 100, $this->error );
				exit();
			}

			$this->registry->DB()->insert( 'blog_entries', $this->entry );
			$entry_id = $this->registry->DB()->getInsertId();

			$this->registry->blogFunctions->rebuildBlog( $this->blog['blog_id'] );

			//-----------------------------------------
			// Load and config POST class
			//-----------------------------------------
			
			require_once( BLOG_PATH . "sources/classes/post/blogPost.php" );
			require_once( BLOG_PATH . "sources/classes/post/entry_new_entry.php" );
			
			$this->lib_post				=  new postFunctions( $this->registry );
			$this->lib_post->blog		= & $this->blog;

			//-------------------------------------------------
			// If the entry is published, run the Blog tracker and pings
			//-------------------------------------------------

			$this->lib_post->blogTracker( $entry_id, $this->entry['entry_name'], $this->entry['entry'] );
			$this->lib_post->blogPings ( $entry_id, $this->entry );

			//-------------------------------------------------
			// Update the Blog stats
			//-------------------------------------------------
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
	 * @param	string  $appkey       	   			Appkey (ignored)
	 * @param	int		$postid       	   			Entry ID
	 * @param	string	$username					Username
	 * @param	string	$password					Password
	 * @param	string	$content					Post content
	 * @param	bool	$publish					Publish?
	 * @return	string	xml
	 **/
	public function editPost( $appkey, $postid, $username, $password, $content, $publish )
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_portal' ), 'blog' );
		
		//-----------------------------------------
		// Authenticate
		//-----------------------------------------
		if ( $this->_authenticate( $username, $password ) )
		{
			//-----------------------------------------
			// find the entry
			//-----------------------------------------
		    $entry = $this->registry->DB()->buildAndFetch( array ( 'select'	=>	'*',
														   'from'	=>	'blog_entries',
														   'where'	=>	"entry_id = ".intval($postid)
												 )		 );
			if ( ! $entry['entry_id'] )
			{
				$this->classApiServer->apiSendError( 100, sprintf( $this->registry->class_localization->words['blogger_error_3'], intval($postid) ) );
				exit();
			}

			//-----------------------------------------
			// return
			//-----------------------------------------
			if ( ! $this->_compilePost( $content['description'], $publish ) )
			{
				$this->classApiServer->apiSendError( 100, $this->error );
				exit();
			}

			$this->entry['blog_id']				= $entry['blog_id'];
			$this->entry['entry_author_id']		= $entry['entry_author_id'];
			$this->entry['entry_author_name']	= $entry['entry_author_name'];
			$this->entry['entry_date']			= $entry['entry_date'];
			$this->entry['entry_last_update']	= $entry['entry_last_update'];
			$this->entry['entry_edit_time']		= time();
			$this->entry['entry_edit_name'] 	= $this->memberData['members_display_name'];
			$this->entry['entry_poll_state']	= $entry['entry_poll_state'];

			//-------------------------------------------------
			// Update entry in DB
			//-------------------------------------------------
			$this->registry->DB()->update( 'blog_entries', $this->entry, 'entry_id='.$entry['entry_id'] );

			//-----------------------------------------
			// Rebuild the Blog
			//-----------------------------------------
			$this->registry->blogFunctions->rebuildBlog ( $this->blog['blog_id'] );

			//-----------------------------------------
			// Return true
			//-----------------------------------------
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
	 * _authenticate()
	 *
	 * Authenticates the username and password
	 *
	 * This will return
	 * - false	(Failed)
	 * - true	(Succes)
	 *
	 * @access	private
	 * @param	string  $username       	   		Username
	 * @param	string  $password					Password
	 * @return	boolean
	 **/
	private function _authenticate( $username, $password )
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_portal' ), 'blog' );
		
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

		if (ipsRegistry::$settings['board_offline'] == 1)
		{
			if ($this->memberData['g_access_offline'] != 1)
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
		
		$blog = $this->registry->DB()->buildAndFetch( array( 
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
			$this->error = $this->blogFunctions->error;
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
		if ( !ipsRegistry::$settings['blog_allow_xmlrpc'] or !$this->blog['blog_settings']['enable_xmlrpc'] )
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
				$this->registry->DB()->update( 'blog_blogs', array( 'blog_settings' => $blog_settings ), "blog_id = {$this->blog['blog_id']}" );
			}
			else
			{
				$this->blog['blog_settings']['xmlrpc_failedattempts'] = isset( $this->blog['blog_settings']['xmlrpc_failedattempts'] ) ? intval($this->blog['blog_settings']['xmlrpc_failedattempts'])+1 : 1;
				$blog_settings = serialize ( $this->blog['blog_settings'] );
				$this->registry->DB()->update( 'blog_blogs', array( 'blog_settings' => $blog_settings ), "blog_id = {$this->blog['blog_id']}" );
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
				$this->registry->DB()->update( 'blog_blogs', array( 'blog_settings' => $blog_settings ), "blog_id = {$this->blog['blog_id']}" );
			}
		}

		//-----------------------------------------
		// Set the member data
		//-----------------------------------------
		
		$this->memberData	= $member;
		
		return true;
	}

    /*-------------------------------------------------------------------------*/
	// Add an entry to the Mod log
    /*-------------------------------------------------------------------------*/

	public function addModlog( $mod_title )
	{
		$this->registry->DB()->insert( 'moderator_logs', array (
												  'member_id'   => $this->memberData['member_id'],
												  'member_name' => $this->memberData['members_display_name'],
												  'ip_address'  => $this->member->ip_address,
												  'http_referer'=> my_getenv('HTTP_REFERER'),
												  'ctime'       => time(),
												  'action'      => IPSText::mbsubstr( $mod_title, 0, 128 ),
												  'query_string'=> IPSText::mbsubstr( htmlspecialchars(my_getenv('QUERY_STRING')), 0, 128 ),
												)						 );
	}

    /*-------------------------------------------------------------------------*/
	// Compile the entry content
    /*-------------------------------------------------------------------------*/

	private function _compilePost( & $content, $publish )
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_portal' ), 'blog' );
		
		//----------------------------------------------------------------
		// Do we have a valid post?
		//----------------------------------------------------------------
		ipsRegistry::$settings['blog_max_entry_length'] = ipsRegistry::$settings['blog_max_entry_length'] ? ipsRegistry::$settings['blog_max_entry_length'] : 2140000;

		if (strlen( trim( IPSText::br2nl( $content['description'] ) ) ) < 1)
		{
			$this->error = $this->registry->class_localization->words['blogger_emptypost'];
			return false;
		}

		if (strlen( $content ) > (ipsRegistry::$settings['blog_max_entry_length']*1024))
		{
			$this->error = $this->registry->class_localization->words['blogger_toolong'];
			return false;
		}

		//-------------------------------------------------
		// check to make sure we have a valid entry title
		//-------------------------------------------------
		$title = $content['title'];

		# Fix &amp;
		$title = str_replace( '&amp;', '&', $title );
		$title = IPSText::parseCleanValue( $title );

		# Fix up &amp;reg;
		$title = str_replace( '&amp;reg;', '&reg;', $title );
		$title = IPSText::getTextClass('parser')->stripBadWords( $title );
		$title = str_replace( "<br>", "", $title );
		$title = trim($title);

		//-------------------------------------------------
		// More unicode..
		//-------------------------------------------------
		$temp = IPSText::stripslashes($title);
		$temp = preg_replace("/&#([0-9]+);/", "-", $temp );
		if ( strlen($temp) > 64 )
		{
			$this->error = $this->registry->class_localization->words['blogger_title_toolong'];
			return false;
		}
		if ( (strlen($temp) < 2) or (!$title)  )
		{
			$this->error = $this->registry->class_localization->words['blogger_notitle'];
			return false;
		}

		//--------------------------------------------
		// Sort post content: Convert HTML to BBCode
		//--------------------------------------------

		IPSText::getTextClass('parser')->parse_smilies		= 1;
		IPSText::getTextClass('parser')->parse_html			= 0;
		IPSText::getTextClass('parser')->parse_bbcode		= 1;
		IPSText::getTextClass('parser')->parsing_section	= 'blog_entry';

		//--------------------------------------------
		// Clean up..
		//--------------------------------------------

		$content = preg_replace( "#<br />(\r)?\n#is", "<br />", $content );

		//-----------------------------------------
		// Post process the editor
		// Now we have safe HTML and bbcode
		//-----------------------------------------

		$content = IPSText::getTextClass('parser')->preDbParse( IPSText::getTextClass('editor')->processRawPost( IPSText::stripslashes($content) ) );

		$dte = time();
		
		/* Build the entry array */
		$this->entry = array(
						'blog_id'			  => $this->blog['blog_id'],
						'entry_author_id'	  => $this->memberData['member_id'],
						'entry_author_name'	  => $this->memberData['members_display_name'],
						'entry_date'		  => $dte,
						'entry_name'		  => $title,
						'entry_name_seo'	  => IPSText::makeSeoTitle($title),
						'entry'     		  => $content,
						'entry_short'		  => $this->registry->blogFunctions->getEntryExcerpt( array( 'entry_short' => '', 'entry_id' => null, 'entry' => $content ) ),
						'entry_status'		  => ($publish ? 'published' : 'draft'),
						'entry_post_key'	  => md5(microtime()),
						'entry_html_state'	  => 0,
						'entry_use_emo'		  => 1,
						'entry_last_update'	  => $dte,
						'entry_gallery_album' => 0,
						'entry_poll_state'    => 0,
					 );

	    // If we had any errors, parse them back to this class
	    // so we can track them later.
		if ( is_array(IPSText::getTextClass('parser')->error) && count(IPSText::getTextClass('parser')->error) > 0 )
		{
	    	$this->error = implode( " : ", IPSText::getTextClass('parser')->error );
	    	return false;
	    }

		return true;
	}

}