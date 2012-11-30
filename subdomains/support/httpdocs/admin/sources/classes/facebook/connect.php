<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Twitter Connect Library
 * Created by Matt Mecham
 * Last Updated: $Date: 2010-07-15 21:31:17 -0400 (Thu, 15 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6661 $
 *
 */

class facebook_connect
{
	/**#@+
	* Registry Object Shortcuts
	*
	* @access	protected
	* @var		object
	*/
	protected $DB;
	protected $settings;
	protected $lang;
	protected $member;
	protected $memberData;
	protected $cache;
	protected $caches;
	/**#@-*/
	
	/**
	 * Facebook wrapper
	 *
	 * @access	private
	 * @var		object
	 */
	private $_api;
	
	/**
	 * Facebook OAUTH wrapper
	 *
	 * @access	private
	 * @var		object
	 */
	private $_oauth;
	
	/**
	 * IPBs log in handler
	 *
	 * @access	private
	 * @var		object
	 */
	private $_login;
	
	/**
	 * User connected
	 * 
	 * @access	private
	 * @var		boolean
	 */
	private $_connected = false;
	
	/**
	 * User: Token
	 *
	 * @access	private
	 * @var		string
	 */
	private $_userToken;
	
	/**
	 * User: ID
	 *
	 * @access	private
	 * @var		int
	 */
	private $_userId;
	
	/**
	 * User: Data
	 *
	 * @access	private
	 * @var		array
	 */
	private $_userData = array();
	
	/**
	 * Required permissions
	 *
	 * @access	public
	 * @var		array
	 */
	public $extendedPerms = array( 'email', 'read_stream', 'publish_stream', 'offline_access' );
	
	/**
	 * Construct.
	 * $this->memberData['twitter_token'] $this->memberData['twitter_secret']
	 * @param	object		Registry object
	 * @param	string		Facebook user token
	 * @param	int			User ID
	 * @param	boolean		Force an exception to be thrown rather than output error (used in IPSMember::buildDisplayPhoto)
	 * @access	public
	 * @return	void
	 */
	public function __construct( $registry, $token='', $userId=0, $forceThrowException=false )
	{
		/* Make object */
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
		
		define("FACEBOOK_APP_ID"      , trim( $this->settings['fbc_appid'] ) );
		define("FACEBOOK_APP_SECRET"  , trim( $this->settings['fbc_secret'] ) );
		define("FACEBOOK_CALLBACK"    , $this->settings['_original_base_url'] . '/interface/facebook/index.php?m=' . $this->memberData['member_id'] );
		
		/* Auto do it man */
		if ( ! $token AND $this->memberData['member_id'] AND $this->memberData['fb_token'] )
		{
			$token  = $this->memberData['fb_token'];
		}
		
		/* Auto do it man */
		if ( ! $userId AND $this->memberData['member_id'] AND $this->memberData['fb_uid'] )
		{
			$userId  = $this->memberData['fb_uid'];
		}
		
		$this->_userToken  = trim( $token );
		$this->_userId     = trim( $userId ); /* never int - max ids are larger than int */
		
		/* Test */
		if ( ! FACEBOOK_APP_ID OR ! FACEBOOK_APP_SECRET )
		{
			/* Give upgraders a helping hand */
			if ( ! FACEBOOK_APP_ID )
			{ 
				if ( $forceThrowException === false )
				{ 
					$this->registry->output->showError( $this->lang->words['gbl_fb_no_app_id'], 911 );
				}
				else
				{ 
					throw new Exception( 'FACEBOOK_NO_APP_ID' );
				}
			}
			else
			{
				throw new Exception( 'FACEBOOK_NOT_SET_UP' );
			}
		}
		
		/* Reset the API */
		$this->resetApi( $token, $userId );
	}
	
	/**
	 * Resets API
	 *
	 * @access	public
	 * @param	string		OAUTH user token
	 */
	public function resetApi( $token='', $userId='' )
	{
		$this->_userToken  = trim( $token );
		$this->_userId     = trim( $userId );
		
		/* A user token is always > 32 */
		if ( strlen( $this->_userToken ) <= 32 )
		{
			/* we store a tmp md5 key during auth, so ensure we don't pass this as a token */
			$this->_userToken = '';
		}
		
		/* Load oAuth */
		require_once( IPS_KERNEL_PATH . 'facebook/facebookoauth.php' );
		$this->_oauth = new FacebookOAuth( FACEBOOK_APP_ID, FACEBOOK_APP_SECRET, FACEBOOK_CALLBACK, $this->extendedPerms );
		
		/* Load API */
		require_once( IPS_KERNEL_PATH . 'facebook/facebook.php' );
		$this->_api = new Facebook( array( 'appId' => FACEBOOK_APP_ID, 'secret' => FACEBOOK_APP_SECRET, 'cookie' => true ) );
		
		if ( $this->_userToken AND $this->_userId  )
		{
			try
			{
				$_userData = $this->_api->api('me', array( 'access_token' => $this->_userToken ) );
			}
			catch( Exception $e ){}
			
			if ( $_userData['id'] AND $_userData['id'] == $this->_userId )
			{
				$this->_userData  = $_userData;
				$this->_connected = true;
			}
			else
			{
				$this->_userData  = array();
				$this->_connected = false;
			}
		}
		else
		{
			$this->_userData  = array();
			$this->_connected = false;
		}
	}
	
	/**
	 * Revoke app authorization
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function revokeAuthorization()
	{
		if ( $this->_userToken AND $this->_userId )
		{
			try
			{
				$val = $this->_api->api( array( 'method' => 'auth.revokeAuthorization', 'access_token' => $this->_userToken, 'uid' => $this->_userId ) );
			}
			catch( Exception $e ){}
		}
		
		return $val;
	}

	
	/**
	 * User has removed app from Facebook
	 * @link http://wiki.developers.facebook.com/index.php/Post-Remove_Callback_URL
	 *
	 * @access	public
	 */
	public function userHasRemovedApp()
	{
		/* INIT */
		$sig    = '';
		$userId = intval( $_POST['fb_sig_user'] );
		
		/* Generate signature */
		ksort($_POST);
		
		foreach( $_POST as $key => $val )
		{
    		if ( substr( $key, 0, 7 ) == 'fb_sig_' )
    		{
        		$sig .= substr( $key, 7 ) . '=' . $val;
    		} 
		}

		$sig   .= FACEBOOK_APP_SECRET;
		$verify = md5($sig);
	
		if ( $userId AND $verify == $_POST['fb_sig'] )
		{
			/* Load user */
			$_member = IPSMember::load( $userId, 'all', 'fb_uid' );
			
			if ( $_member['member_id'] )
			{
   				/* Remove any FB stuffs */
   				IPSMember::save( $_member['member_id'], array( 'core' => array( 'fb_uid' => 0, 'fb_lastsync' => 0, 'fb_session' => '', 'fb_emailhash' => '', 'fb_token' => '' ) ) );
   	   		}
   	   	}
   	}
	
	/**
	 * Fetch user has app permission
	 * Wrapper so we can change it later
	 *
	 * @access	public
	 * @param	string	Permission mask ('email', etc)
	 * @return	boolean
	 */
	public function fetchHasAppPermission( $permission )
	{
		if ( $this->_userToken AND $this->_userId )
		{
			try
			{
				$val = $this->_api->api( array( 'method' => 'users.hasAppPermission', 'access_token' => $this->_userToken, 'uid' => $this->_userId, 'ext_perm' => $permission ) );
			}
			catch( Exception $e ){}
		}
		
		return $val;
	}
	
	/**
	 * Return user data
	 *
	 * @access	public
	 * @return	array
	 */
	public function fetchUserData( $token='' )
	{
		$token = ( $token ) ? $token : $this->_userToken;
		
		if ( $token AND is_array( $this->_userData ) AND $this->_userData['id'] AND ! isset( $this->_userData['pic'] ) )
		{
			/* Query extra data - returns annoying https images */
			try
			{
				$updates = $this->_api->api( array( 'method'       => 'fql.query',
	        									    'query'        => 'select pic_small, pic_big, pic_square, pic, timezone, sex from user where uid=' . $this->_userData['id'],
	        									    'access_token' => $token ) );
        	}
        	catch( Exception $e ){}
        									    
        	
        	if ( count( $updates[0] ) )
        	{
        		foreach( $updates[0] as $k => $v )
        		{
        			$this->_userData[ $k ] = $v;
        		}
        	}
        	
        	/* Now fetch about information */
        	try
        	{
        		$aboutme = $this->_api->api( $this->_userData['id'], 'GET', array( 'access_token' => $token ) );
    		}
    		catch( Exception $e ){}
        	
        	if ( count( $aboutme ) )
        	{
        		foreach( $aboutme as $k => $v )
        		{
        			if ( $k == 'about' )
        			{
        				$v = nl2br( $v );
        			}
        			
        			$this->_userData[ $k ] = $v;
        		}
        	}
		}
		
		return $this->_userData;
	}
	
	/**
	 * Return whether or not the user is connected to twitter
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isConnected()
	{
		return ( $this->_connected == true ) ? true : false;
	}
	
	/**
	 * Post a link to the user's FB wall
	 *
	 * @access	public
	 * @param	string		URL
	 * @param	string		Comment (can be NUFING)
	 */
	public function postLinkToWall( $url, $comment='' )
	{
		$memberData = $this->memberData;
		
		/* Got a member? */
		if ( ! $memberData['member_id'] )
		{
			throw new Exception( 'NO_MEMBER' );
		}
		
		/* Linked account? */
		if ( ! $memberData['fb_uid'] OR ! $memberData['fb_token'] )
		{
			throw new Exception( 'NOT_LINKED' );
		}
				
		/* POST the data */
		try
		{
			$this->_api->api( 'me/links', 'POST', array( 'access_token' => $this->_userToken, 'link' => $url, 'message' => $comment ) );
		}
		catch( Exception $e ){}
	}
	
	/**
	 * Post a status update to Facebook based on native content
	 * Which may be longer and such and so on and so forth, etc
	 *
	 * @access	public
	 * @param	string		Content
	 * @param	string		URL to add
	 * @param	bool		Always add the URL regardless of content length
	 */
	public function updateStatusWithUrl( $content, $url, $alwaysAdd=false )
	{
		$memberData = $this->memberData;
		
		/* Got a member? */
		if ( ! $memberData['member_id'] )
		{
			throw new Exception( 'NO_MEMBER' );
		}
		
		/* Linked account? */
		if ( ! $memberData['fb_uid'] )
		{
			throw new Exception( 'NOT_LINKED' );
		}
		
		/* Ensure content is correctly de-html-ized */
		$content = IPSText::UNhtmlspecialchars( $content );
		
		/* Ensure it's converted cleanly into utf-8 */
    	$content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
    	
		/* Is the text longer than 140 chars? */
		if ( $alwaysAdd === TRUE or IPSText::mbstrlen( $content ) > 500 )
		{
			/* Leave 26 chars for URL shortener */
			$content = IPSText::mbsubstr( $content, 0, 474 ) . '...';
			
			/* Generate short URL */
			require_once( IPS_ROOT_PATH . 'sources/classes/url/shorten.php' );
 			$shorten = new urlShorten();
 			
 			$data      = $shorten->shorten( $url, 'bitly' );
 			
 			$content .= ' ' . $data['url'];
		}
		
		/* POST the data */
		try
		{
			$this->_api->api( array( 'method' => 'users.setStatus', 'access_token' => $this->_userToken, 'uid' => $this->_userId, 'status' => $content, 'status_includes_verb' => true ) );
		}
		catch( Exception $e ){}
	}
	
	/**
	 * Redirects a user to the oauth connect page.
	 *
	 * @access	public
	 * @return	redirect
	 */
	public function redirectToConnectPage()
	{
		/* Reset api to ensure user is not logged in */
		$this->resetApi();
		
		/* Append OAUTH URL */
		$_urlExtra = '';
		$key       = md5( uniqid( microtime() ) );
		$_urlExtra = '&key=' . $key;
		
		if ( $this->request['reg'] )
		{
			$_urlExtra .= '&reg=1';
		}
		
		/* Update user's row */
		if ( $this->memberData['member_id'] )
		{
			IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'fb_token'  => $key ) ) );
		}
		
		/* Update callback url */
		$this->_oauth->setCallBackUrl( FACEBOOK_CALLBACK . $_urlExtra );
		
		$url = $this->_oauth->getAuthorizeURL();
		$this->registry->output->silentRedirect( $url );
	}
	
	/**
	 * Completes the connection
	 *
	 * @access	public
	 * @return	redirect
	 */
	public function finishLogin()
	{
		/* From reg flag */
		if ( $_REQUEST['code'] )
		{
			/* Load oAuth */
			require_once( IPS_KERNEL_PATH . 'facebook/facebookoauth.php' );
			$this->_oauth = new FacebookOAuth( FACEBOOK_APP_ID, FACEBOOK_APP_SECRET, FACEBOOK_CALLBACK, $this->extendedPerms );
			
			/* Load API */
			require_once( IPS_KERNEL_PATH . 'facebook/facebook.php' );
			$this->_api = new Facebook( array( 'appId' => FACEBOOK_APP_ID, 'secret' => FACEBOOK_APP_SECRET, 'cookie' => true ) );
			
			/* Ensure URL is correct */
			$_urlExtra = '';
			
			if ( $_REQUEST['key'] )
			{
				$_urlExtra .= '&key=' . $_REQUEST['key'];
			}
			
			if ( $_REQUEST['reg'] )
			{
				$_urlExtra .= '&reg=1';
			}
			
			/* Update callback url */
			$this->_oauth->setCallBackUrl( FACEBOOK_CALLBACK . $_urlExtra );

			/* Generate oAuth token */
			$rToken = $this->_oauth->getAccessToken( $_REQUEST['code'] );
			
			if ( is_string( $rToken ) )
			{
				try
				{
					$_userData = $this->_api->api('me', array( 'access_token' => $rToken ) );
				}
				catch( Exception $e ){}
				
				/* A little gymnastics */
				$this->_userData = $_userData;
				$_userData = $this->fetchUserData( $rToken );
				
				/* Got a member linked already? */
				$_member = IPSMember::load( $_userData['id'], 'all', 'fb_uid' );
				
				/* Not connected, check email address */
				if ( ! $_member['member_id'] AND $_userData['email'] )
				{
					$_member = IPSMember::load( $_userData['email'], 'all', 'email' );
					
					/* We have an existing account, so trash email forcing user to sign up with new */
					if ( $_member['member_id'] )
					{
						unset( $_member );
						unset( $_userData['email'] );
					}
				}
				
				if ( $_member['member_id'] )
				{
					$memberData = $_member;
					
					/* Ensure user's row is up to date */
					IPSMember::save( $memberData['member_id'], array( 'core' => array( 'fb_token' => $rToken ) ) );
					    
					/* Here, so log us in!! */
					$data = $this->_login()->loginWithoutCheckingCredentials( $memberData['member_id'], TRUE );
					
					$this->registry->getClass('output')->silentRedirect( $data[1] ? $data[1] : $this->settings['base_url'] );
				}
				else
				{
					/* No? Create a new member */
					foreach( array( 'fbc_s_pic', 'fbc_s_avatar', 'fbc_s_status', 'fbc_s_aboutme' ) as $field )
					{
						$toSave[ $field ] = 1;
					}

					$fb_bwoptions = IPSBWOptions::freeze( $toSave, 'facebook' );
					$safeFBName   = str_replace( ' ', '', IPSText::convertCharsets( $_userData['name'], 'utf-8', IPS_DOC_CHAR_SET ) );
					$displayName  = ( ! $this->settings['auth_allow_dnames'] ) ? $safeFBName : FALSE;
					
					/* If we're not allowing display names, then use may not hit partial form, so.. */
					if ( ! $this->settings['auth_allow_dnames'] AND $displayName AND $_userData['email'] AND $_userData['id'] )
					{
						/* Remote registrations disabled? */
						if ( $this->settings['no_reg'] == 2 )
						{
							$this->registry->output->showError( 'no_remote_reg', 1090002 );
						}
					}
		
					/* From reg, so create new account properly */
					$toSave = array( 'core' 		 => array(  'name' 				     => $safeFBName,
													 		    'members_display_name'   => $displayName,
													 		    'members_created_remote' => 1,
													 		    'member_group_id'		 => ( $this->settings['fbc_mgid'] ) ? $this->settings['fbc_mgid'] : $this->settings['member_group'],
															    'email'                  => $_userData['email'],
															    'fb_uid'                 => $_userData['id'],
															    'time_offset'            => $_userData['timezone'],
															    'fb_token'               => $rToken ),
									'extendedProfile' => array( 'pp_about_me'            => IPSText::getTextClass( 'bbcode' )->stripBadWords( IPSText::convertCharsets( $_userData['about'], 'utf-8', IPS_DOC_CHAR_SET ) ),
																'fb_photo'        		 => $_userData['pic_big'],
																'fb_photo_thumb'  		 => $_userData['pic_square'],
																'fb_bwoptions'    		 => $fb_bwoptions,
																'avatar_location' 		 => $_userData['pic_square'],
																'avatar_type'     		 => 'facebook' ) );
	
					
					$memberData = IPSMember::create( $toSave, TRUE, FALSE, FALSE );
					
					if ( ! $memberData['member_id'] )
					{
						throw new Exception( 'CREATION_FAIL' );
					}
					
					$pmember = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'members_partial', 'where' => "partial_member_id=" . $memberData['member_id'] ) );

					if ( $pmember['partial_member_id'] )
					{
						$this->registry->getClass('output')->silentRedirect( $this->settings['base_url'] . 'app=core&module=global&section=register&do=complete_login&mid='. $memberData['member_id'].'&key='.$pmember['partial_date'] );
					}
					else
					{
						/* Already got a display name */
						if ( $displayName )
						{
							/* Here, so log us in!! */
							$data = $this->_login()->loginWithoutCheckingCredentials( $memberData['member_id'], TRUE );
					
							$this->registry->getClass('output')->silentRedirect( $data[1] ? $data[1] : $this->settings['base_url'] );
						}
						else
						{
							throw new Exception( 'CREATION_FAIL' );
						}
					}
				}
			}
			else
			{
				throw new Exception( 'CREATION_FAIL' );
			}
		}
	}
	
	/**
	 * Completes the connection
	 *
	 * @access	public
	 * @return	redirect
	 */
	public function finishConnection()
	{
		if ( $_REQUEST['m'] AND $_REQUEST['code'] )
		{
			/* Load user */
			$member = IPSMember::load( intval( $_REQUEST['m'] ) );
		
			if ( $member['fb_token'] == $_REQUEST['key'] )
			{
				/* Load oAuth */
				require_once( IPS_KERNEL_PATH . 'facebook/facebookoauth.php' );
				$this->_oauth = new FacebookOAuth( FACEBOOK_APP_ID, FACEBOOK_APP_SECRET, FACEBOOK_CALLBACK, $this->extendedPerms );
				
				/* Load API */
				require_once( IPS_KERNEL_PATH . 'facebook/facebook.php' );
				$this->_api = new Facebook( array( 'appId' => FACEBOOK_APP_ID, 'secret' => FACEBOOK_APP_SECRET, 'cookie' => true ) );
				
				/* Ensure URL is correct */
				$_urlExtra = '';
				
				if ( $_REQUEST['key'] )
				{
					$_urlExtra .= '&key=' . $_REQUEST['key'];
				}
				
				if ( $_REQUEST['reg'] )
				{
					$_urlExtra .= '&reg=1';
				}
				
				/* Update callback url */
				$this->_oauth->setCallBackUrl( FACEBOOK_CALLBACK . $_urlExtra );
			
				/* Generate oAuth token */
				$rToken = $this->_oauth->getAccessToken( $_REQUEST['code'] );

				if ( is_string( $rToken ) )
				{
					try
					{
						$_userData = $this->_api->api('me', array( 'access_token' => $rToken ) );
					}
					catch( Exception $e ){}
					
					/* Ensure user's row is up to date */
					IPSMember::save( $member['member_id'], array( 'core' => array( 'fb_uid'    => $_userData['id'],
																				   'fb_token'  => $rToken ) ) );
				}
			}
		}
		
		/* Redirect back to settings page */
		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url'] . 'app=core&module=usercp&tab=members&area=facebook' );
	}

	/**
	 * Finish a log-in connection
	 * WARNING: NO PERMISSION CHECKS ARE PERFORMED IN THIS FUNCTION.
	 *
	 * @access		public
	 * @param		int			Forum ID of original member (member to keep)
	 * @param		int			Forum ID of linking member  (member to remove)
	 * @return		boolean
	 */
	public function finishNewConnection( $originalId, $newId )
	{
		if ( $originalId AND $newId )
		{
			$original = IPSMember::load( $originalId, 'all' );
			$new      = IPSMember::load( $newId, 'all' );
			
			if ( $original['member_id'] AND $new['fb_uid'] AND $new['fb_token'] )
			{
				IPSMember::save( $original['member_id'], array( 'core' => array( 'fb_uid' => $new['fb_uid'], 'fb_token' => $new['fb_token'] ) ) );
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Function to resync a member's Twitter data
	 *
	 * @access	public
	 * @param	mixed		Member Data in an array form (result of IPSMember::load( $id, 'all' ) ) or a member ID
	 * @return	array 		Updated member data	
	 *
	 * EXCEPTION CODES:
	 * NO_MEMBER		Member ID does not exist
	 * NOT_LINKED		Member ID or data specified is not linked to a FB profile
	 */
	public function syncMember( $memberData )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$exProfile = array();
		
		/* Do we need to load a member? */
		if ( ! is_array( $memberData ) )
		{
			$memberData = IPSMember::load( intval( $memberData ), 'all' );
		}
		
		/* Got a member? */
		if ( ! $memberData['member_id'] )
		{
			throw new Exception( 'NO_MEMBER' );
		}
		
		/* Linked account? */
		if ( ! $memberData['fb_uid'] )
		{
			throw new Exception( 'NOT_LINKED' );
		}
		
		/* Thaw Options */
		$bwOptions = IPSBWOptions::thaw( $memberData['fb_bwoptions'], 'facebook' );
		
		/* Grab the data */
		try
		{
			$this->resetApi( $memberData['fb_token'], $memberData['fb_uid'] );
			
			if ( $this->isConnected() )
			{
				$user = $this->fetchUserData();
				
				/* Update.. */
				$exProfile['fb_photo']       = ( $bwOptions['fbc_s_pic'] ) ? $user['pic_big']    : '';
				$exProfile['fb_photo_thumb'] = ( $bwOptions['fbc_s_pic'] ) ? $user['pic_square'] : '';
			
				if ( $bwOptions['fbc_s_avatar'] )
				{
					$exProfile['avatar_location'] = $user['pic_square'];
					$exProfile['avatar_type']     = 'facebook';
				}
			
				if ( $bwOptions['fbc_s_aboutme'] )
				{
					$exProfile['pp_about_me'] = IPSText::getTextClass( 'bbcode' )->stripBadWords( IPSText::convertCharsets( $user['about'], 'utf-8', IPS_DOC_CHAR_SET ) );
				}
				
				if ( $bwOptions['fbc_si_status'] AND ( isset( $memberData['gbw_no_status_import'] ) AND ! $memberData['gbw_no_status_import'] ) )
				{
					/* Fetch timeline */
					//$memberData['tc_last_sid_import'] = ( $memberData['tc_last_sid_import'] < 1 ) ? 100 : $memberData['tc_last_sid_import'];
					$_updates = $this->fetchUserTimeline( $user['id'], 0, true );
					
					/* Got any? */
					if ( count( $_updates ) )
					{
						$update = array_shift( $_updates );
						
						if ( is_array( $update ) AND isset( $update['message'] ) )
						{
							/* Load status class */
							if ( ! $this->registry->isClassLoaded( 'memberStatus' ) )
							{
								$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/status.php', 'memberStatus' );
								$this->registry->setClass( 'memberStatus', new $classToLoad( ipsRegistry::instance() ) );
							}
							
							/* Set Author */
							$this->registry->getClass('memberStatus')->setAuthor( $memberData );
							
							/* Convert if need be */
							if ( IPS_DOC_CHAR_SET != 'UTF-8' )
							{
								$update['message'] = IPSText::utf8ToEntities( $update['message'] );
							}
							
							/* Set Content */
							$this->registry->getClass('memberStatus')->setContent( trim( IPSText::getTextClass( 'bbcode' )->stripBadWords( $update['message'] ) ) );
							
							/* Set as imported */
							$this->registry->getClass('memberStatus')->setIsImport( 1 );
							
							/* Set creator */
							$this->registry->getClass('memberStatus')->setCreator( 'facebook' );
		
							/* Can we reply? */
							if ( $this->registry->getClass('memberStatus')->canCreate() )
					 		{
								$this->registry->getClass('memberStatus')->create();
								
								//$exProfile['tc_last_sid_import'] = $update['id'];
							}
						}
					}
				}
				
				/* Update member */
				IPSMember::save( $memberData['member_id'], array( 'core' 			=> array( 'fb_lastsync' => time() ),
																  'extendedProfile' => $exProfile ) );
			
				/* merge and return */
				$memberData['fb_lastsync'] = time();
				$memberData = array_merge( $memberData, $exProfile );
			}
		}
		catch( Exception $e )
		{
		}
		
		return $memberData;
	}
	
	/**
	 * Fetch a user's recent status updates (max 50)
	 *
	 * @access	public
	 * @param	int		Twitter ID
	 * @param	bool	Strip @replies (true default)
	 * @param	int		Minimum ID to grab from
	 * @return	array
	 */
	public function fetchUserTimeline( $userId=0, $minId=0, $stripReplies=true )
	{
		$userId = ( $userId ) ? $userId : $this->_userData['id'];
		$count  = 50;
		$final  = array();
		
		if ( $this->_userToken AND $userId )
		{
			try
			{
				$updates = $this->_api->api( array( 'method'       => 'fql.query',
		        									'query'        => 'select uid,status_id,message from status where uid=' . $userId . ' and status_id > ' . $minId . ' ORDER BY time DESC LIMIT 0, ' . $count,
		        									'access_token' => $this->_userToken ) );
	        }
	        catch( Exception $e ){}
			
			if ( is_array( $updates ) AND count( $updates ) )
			{
				foreach( $updates as $update )
				{
					$final[] = $update;
				}
			}
		}
		
		return $final;
	}
	
	/**
	 * Accessor for the log in functions
	 *
	 * @access	public
	 * @return	object
	 */
	public function _login()
	{
		if ( ! is_object( $this->_login ) )
		{
			require_once( IPS_ROOT_PATH . 'sources/handlers/han_login.php' );
	    	$this->_login =  new han_login( $this->registry );
	    	$this->_login->init();
		}
		
		return $this->_login;
	}
	
}