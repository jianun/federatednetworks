<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Mobile API
 * Last Updated: $Date: 2010-07-10 01:01:06 -0400 (Sat, 10 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2008 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6628 $
 *
 */

define( 'IPB_THIS_SCRIPT', 'public' );
require_once( '../../initdata.php' );
		
require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );

class mobileApiRequest
{
	/**#@+
	 * Registry Object Shortcuts
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
	protected $memberData;
	protected $cache;
	protected $caches;
	
	/**
	 * Make the registry shortcuts
	 *
	 * @access	public
	 * @param	object	ipsRegistry reference
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
	}
	
	/**
	 * Figure out what api is being called
	 *
	 * @access	public
	 * @return	void
	 */
	public function dispatch()
	{
		/* Force a cookie to identify as a mobile app */
		IPSCookie::set("mobileApp", 'true', -1);
		
		/* Figure out the action */
		switch( $this->request['api'] )
		{
			case 'getNotifications':
				$this->_handleGetNotifications();
			break;
			
			case 'toggleNotifications':
				$this->_handleToggleNotifications();
			break;
			
			case 'toggleNotificationKey':
				$this->_hanldeToggleNotificaionKey();
			break;
			
			case 'notificationTypes':
				$this->_handleNotificationTypes();
			break;
			
			case 'login':
				$this->_handleLogin();
			break;
			
			case 'postImage':
				$this->_handlePostImage();
			break;
			
			case 'postStatus':
				$this->_handlePostStatus();
			break;
			
			default:
				$this->_invalidApi();
			break;
		}
	}
	
	/**
	 * Returns a list of unread notifications
	 *
	 * @return	string		XML
	 */
	protected function _handleGetNotifications()
	{
		/* INIT */
		$unreadOnly = ( $this->request['unread'] == 1 || ! isset( $this->request['unread'] ) ) ? true : false;
		
		/* Make sure we're logged in */
		if( ! $this->memberData['member_id'] )
		{
			$this->_returnError( "You're no longer logged in" );
		}
		
		/* Check the form hash */
		if( $this->member->form_hash != $this->request['form_hash'] )
		{
			$this->_returnError( "Invalid Request" );
		}
		
		/* Load the library */
		$classToLoad		= IPSLib::loadLibrary( IPS_ROOT_PATH . '/sources/classes/member/notifications.php', 'notifications' );
		$notifyLibrary		= new $classToLoad( $this->registry );
		$notifyLibrary->setMember( $this->memberData );
		
		/* Fetch the notifications */
		$_data = $notifyLibrary->fetchUnreadNotifications( 10, 'notify_sent', 'DESC', $unreadOnly, true );

		/* XML Parser */
		require_once( IPS_KERNEL_PATH . 'classXML.php' );
		$xml = new classXML( 'utf-8' );
		$xml->newXMLDocument();
		
		/* Build Document */
		$xml->addElement( 'notifications' );

		/* Loop through list */
		if( is_array( $_data ) && count( $_data ) )
		{
			foreach( $_data as $r )
			{
				$xml->addElementAsRecord( 'notifications', array( 'notification' ), array( 
																							'id'			=> $r['notify_id'], 
																							'dateSent'		=> $this->registry->class_localization->getDate( $r['notify_sent'], 'short' ),
																							'notifyTitle'	=> strip_tags( $r['notify_title'] ),
																							'notifyMessage'	=> $r['notify_text'],
																							'notifyURL'		=> $r['notify_url'],
																							'notifyIcon'	=> $r['notify_icon']
																						) 
										);
			}
		}
		
		/* Output */
		echo $xml->fetchDocument();
		exit();
	}
	
	/**
	 * Toggles a specific notification key for a user
	 *
	 * @return	string		XML
	 */
	protected function _hanldeToggleNotificaionKey()
	{
		/* INIT */
		$notifyKey		= $this->request['key'];
		$notifyStatus	= $this->request['status'];
		
		/* Check the form hash */
		if( $this->member->form_hash != $this->request['form_hash'] )
		{
			$this->_returnError( "Invalid Request" );
		}
		
		/* Notifications Library */
		$classToLoad		= IPSLib::loadLibrary( IPS_ROOT_PATH . '/sources/classes/member/notifications.php', 'notifications' );
		$notifyLibrary		= new $classToLoad( $this->registry );
		$notifyLibrary->setMember( $this->memberData );

		/* Notifications Data */
		$_notifyConfig	= $notifyLibrary->getMemberNotificationConfig();

		if( $notifyStatus )
		{
			$_notifyConfig[ $notifyKey ][ 'selected' ][] = 'mobile';
		}
		else
		{
			$_newConfig = array();
			
			foreach( $_notifyConfig[ $notifyKey ][ 'selected' ] as $_v )
			{
				if( $_v != 'mobile' )
				{
					$_newConfig[] = $_v;
				}
			}
			
			$_notifyConfig[ $notifyKey ][ 'selected' ] = $_newConfig;
		}

		/* Save */
		IPSMember::packMemberCache( $this->memberData['member_id'], array( 'notifications' => $_notifyConfig ), $this->memberData['members_cache'] );
	}
	
	/**
	 * Toggles notifications on/off for logged in user
	 *
	 * @return	string		XML
	 */
	protected function _handleToggleNotifications()
	{
		/* INIT */
		$ips_mobile_token	= $this->request['token'];
		$enable				= $this->request['enable'];
		
		/* Check the form hash */
		if( $this->member->form_hash != $this->request['form_hash'] )
		{
			$this->_returnError( "Invalid Request" );
		}
		
		/* Make sure we're logged in */
		if( ! $this->memberData['member_id'] )
		{
			$this->_returnError( "You're no longer logged in" );
		}
		
		/* Check to see if notifications are enabled */
		if( ! IPSLib::canReceiveMobileNotifications() )
		{
			$this->_returnError( "You are not authorized to receive mobile notifications" );
		}
		
		/* Update Array */
		$update = array( 'ips_mobile_token' => $enable ? $ips_mobile_token : '' );
		
		/* Update */
		$this->DB->update( 'members', $update, "member_id={$this->memberData['member_id']}" );
	}
	
	/**
	 * Returns a list of notification options
	 *
	 * @return	string		XML
	 */
	protected function _handleNotificationTypes()
	{
		/* Check to see if notifications are enabled */
		if( ! IPSLib::canReceiveMobileNotifications() )
		{
			$this->_returnError( "You are not authorized to receive mobile notifications" );
		}
		
		/* Lang */
		$this->lang->loadLanguageFile( array( 'public_usercp' ), 'core' );
		
		/* Notifications Library */
		$classToLoad		= IPSLib::loadLibrary( IPS_ROOT_PATH . '/sources/classes/member/notifications.php', 'notifications' );
		$notifyLibrary		= new $classToLoad( $this->registry );
		$notifyLibrary->setMember( $this->memberData );
		
		/* Options */
		$_basicOptions	= array( array( 'email', $this->lang->words['notopt__email'] ), array( 'pm', $this->lang->words['notopt__pm'] ), array( 'inline', $this->lang->words['notopt__inline'] ), array( 'mobile', $this->lang->words['notopt__mobile'] ) );
		$_configOptions	= $notifyLibrary->getNotificationData( TRUE );
		$_notifyConfig	= $notifyLibrary->getMemberNotificationConfig();
		$_defaultConfig	= $notifyLibrary->getDefaultNotificationConfig();
		$_formOptions	= array();
		
		foreach( $_configOptions as $option )
		{
			$_thisConfig	= $_notifyConfig[ $option['key'] ];
			
			//-----------------------------------------
			// Determine available options
			//-----------------------------------------
			
			$_available	= array();
			
			foreach( $_basicOptions as $_bo )	// ewwww :P
			{
				if( !is_array($_defaultConfig[ $option['key'] ]['disabled']) OR !in_array( $_bo[0], $_defaultConfig[ $option['key'] ]['disabled'] ) )
				{
					$_available[]	= $_bo;
				}
			}
			
			//-----------------------------------------
			// If none available, at least give inline
			//-----------------------------------------
			
			if( !count($_available) )
			{
				$_available[]	= array( 'inline', $this->lang->words['notify__inline'] );
			}
			
			//-----------------------------------------
			// Start setting data to pass to form
			//-----------------------------------------
			
			$_formOptions[ $option['key'] ]					= array();
			$_formOptions[ $option['key'] ]['key']			= $option['key'];
			
			//-----------------------------------------
			// Rikki asked for this...
			//-----------------------------------------
			
			foreach( $_available as $_availOption )
			{
				$_formOptions[ $option['key'] ]['options'][ $_availOption[0] ]	= $_availOption;
			}
			
			//$_formOptions[ $option['key'] ]['options']		= $_available;
			
			$_formOptions[ $option['key'] ]['defaults']		= $_thisConfig['selected'];
			$_formOptions[ $option['key'] ]['disabled']		= 0;
			
			//-----------------------------------------
			// Don't allow member to configure
			// Still show, but disable on form
			//-----------------------------------------
			
			if( $_defaultConfig[ $option['key'] ]['disable_override'] )
			{
				$_formOptions[ $option['key'] ]['disabled']		= 1;
				$_formOptions[ $option['key'] ]['defaults']		= $_defaultConfig[ $option['key'] ]['selected'];
			}
		}
		
		/* Groups */
		$this->notifyGroups = array(
									'topics_posts'		=> array( 'new_topic', 'new_reply', 'post_quoted' ),
									'status_updates'	=> array( 'reply_your_status', 'reply_any_status', 'friend_status_update' ),
									'profiles_friends'	=> array( 'profile_comment', 'profile_comment_pending', 'friend_request', 'friend_request_pending', 'friend_request_approve' ),
									'private_msgs' 		=> array( 'new_private_message', 'reply_private_message', 'invite_private_message' )
		);
		
		/* XML Parser */
		require_once( IPS_KERNEL_PATH . 'classXML.php' );
		$xml = new classXML( 'utf-8' );
		$xml->newXMLDocument();
		
		/* Build Document */
		$xml->addElement( 'notifications' );
		
		foreach( $this->notifyGroups as $groupKey => $group )
		{
			$xml->addElement( 'group', 'notifications' );
			$xml->addElementasRecord( 'group', array( 'info' ), array( 'groupTitle' => IPSText::UNhtmlspecialchars( $this->lang->words[ 'notifytitle_' . $groupKey ] ) ) );
			$xml->addElement( 'options', 'group' );
			
			foreach( $group as $key )
			{
				if( ! is_array( $_formOptions[$key] ) )
				{
					continue;
				}
				
				/* Set the done flag */
				$_formOptions[$key]['done'] = 1;
				
				/* Set the title */
				$_title = $this->lang->words[ 'notify__short__' . $key ] ? $this->lang->words[ 'notify__short__' . $key ] : $this->lang->words[ 'notify__' . $key ];
				
				/* Add to XML */
				$xml->addElementAsRecord( 'options', array( 'option' ), array( 
																				'optionKey'		=> $key, 
																				'optionTitle'	=> IPSText::UNhtmlspecialchars( $_title ),
																				'optionEnabled'	=> in_array( 'mobile', $_formOptions[$key]['defaults'] ) ? '1' : '0'
																			) 
										);
			}
		}
		
		/* Other Options */
		$xml->addElement( 'group', 'notifications' );
		$xml->addElementasRecord( 'group', array( 'info' ), array( 'groupTitle' => IPSText::UNhtmlspecialchars( $this->lang->words[ 'notifytitle_other' ] ) ) );
		$xml->addElement( 'options', 'group' );
		
		foreach( $_formOptions as $key => $data )
		{
			if( $data['done'] )
			{
				continue;
			}
			
			/* Set the title */
			$_title = $this->lang->words[ 'notify__short__' . $key ] ? $this->lang->words[ 'notify__short__' . $key ] : $this->lang->words[ 'notify__' . $key ];
			
			/* Add to XML */
			$xml->addElementAsRecord( 'options', array( 'option' ), array( 
																			'optionKey'		=> $key, 
																			'optionTitle'	=> IPSText::UNhtmlspecialchars( $_title ),
																			'optionEnabled'	=> in_array( 'mobile', $data['defaults'] ) ? '1' : '0'
																		) 
									);
		}
		

		/* Output */
		echo $xml->fetchDocument();
		exit();
	}
	
	/**
	 * Attempt to login a user to the mobile service
	 *
	 * @return	string		XML
	 */
	protected function _handleLogin()
	{
		/* Load the login handler */
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/handlers/han_login.php', 'han_login' );
		$this->han_login = new $classToLoad( $this->registry );
		$this->han_login->init();

		/* Attempt login */
		$loginResult = $this->han_login->verifyLogin();
		
		/* Fail */
		if( $loginResult[2] )
		{
			$this->_returnError( 'Login Failed' );
		}
		/* Success */
		else
		{
    		$this->_returnXml( array(
    									'success'			=> 1,
    									'gallery'			=> $this->_userHasGallery( $this->han_login->member_data ) ? '1' : '0',
    									'status'			=> $this->_canUpdateStatus( $this->han_login->member_data ) ? '1' : '0',
										'notifications'		=> $this->_userEnabledNotifications( $this->han_login->member_data ) ? '1' : '0',
										'facebook'			=> IPSLib::fbc_enabled() && $this->han_login->member_data['fb_uid'] ? '1' : '0',
										'twitter'			=> IPSLib::twitter_enabled() && $this->han_login->member_data['twitter_id'] ? '1' : '0',
    									'albums'			=> $this->_userAlbums( $this->han_login->member_data ),
										'form_hash'			=> md5( $this->han_login->member_data['email'].'&'.$this->han_login->member_data['member_login_key'].'&'.$this->han_login->member_data['joined'] )
    						)	);
		}
	}
	
	/**
	 * Determines if a user has notifications enabled
	 *
	 * @return	string		XML
	 */
	protected function _userEnabledNotifications( $memberData )
	{
		/* Check to see if notifications are enabled */
		if( ! IPSLib::canReceiveMobileNotifications( $memberData ) )
		{
			return 0;
		}
		
		if( $memberData['ips_mobile_token'] )
		{
			return 1;
		}
		
		return 0;
	}
	
	/**
	 * Determines if a user has gallery
	 *
	 * @param	array		$memberData
	 * @return	integer		1 or 0
	 */
	protected function _userHasGallery( $memberData )
	{
		/* Gallery installed? */
		if( ! IPSLib::appIsInstalled( 'gallery' ) )
		{
			return 0;
		}
		
		/* User has gallery? */
		if( ! $memberData['has_gallery'] )
		{
			return 0;
		}
		
		return 1;
	}
	
	/**
	 * Determines if a user can update their status
	 *
	 * @param	array		$memberData
	 * @return	integer		1 or 0
	 */
	protected function _canUpdateStatus( $memberData )
	{
		/* Load status class */
		if ( ! $this->registry->isClassLoaded( 'memberStatus' ) )
		{
			require_once( IPS_ROOT_PATH . 'sources/classes/member/status.php' );
			$this->registry->setClass( 'memberStatus', new memberStatus( ipsRegistry::instance() ) );
		}
		
		return $this->registry->getClass('memberStatus')->canCreate( $memberData ) ? '1' : '0';
	}
	
	/**
	 * Determines if a user can update their status
	 *
	 * @param	array		$memberData
	 * @return	array		Array of albums
	 */
	protected function _userAlbums( $memberData )
	{
		/* Make sure we have gallery */
		if( ! IPSLib::appIsInstalled( 'gallery' ) )
		{
			return array();
		}
		
		/* Libs */
		define( 'GALLERY_LIBS', IPS_ROOT_PATH . '/applications_addon/ips/gallery/sources/libs/' );
		
		/* Gallery Object */
		require_once( GALLERY_LIBS . 'lib_gallery.php' );
		$this->registry->setClass( 'glib', new lib_gallery( $this->registry ) );
		
		/* Load the category object */
		require_once( GALLERY_LIBS . 'lib_categories.php' );
		$this->registry->setClass( 'category', new lib_categories( $this->registry ) );
		$this->registry->category->normalInit();
		
		$this->registry->glib->category = $this->registry->category;

		/* Album Library */
		require_once( GALLERY_LIBS . 'lib_albums.php' );
		$this->albums = new lib_albums( $this->registry );

		/* Get a list of user albums */
		$this->albums->getUserAlbums( $memberData['member_id'] );
		
		/* Build Albums */
		$albums = array();
		
		foreach( $this->albums->album_lookup as $r )
		{
			$albums[] = array( 'id' => $r['id'], 'name' => $r['name'] );
		}
		
		return $albums;
	}
	
	/**
	 * Attempt to post an image to a user album
	 *
	 * @return	string		XML
	 */
	protected function _handlePostImage()
	{
		/* Check the form hash */
		if( $this->member->form_hash != $this->request['form_hash'] )
		{
			$this->_returnError( "Invalid Request" );
		}
		
		/* Make sure we're logged in */
		if( ! $this->memberData['member_id'] )
		{
			$this->_returnError( "You're no longer logged in" );
		}
		
		/* Make sure we have gallery */
		if( ! IPSLib::appIsInstalled( 'gallery' ) )
		{
			$this->_returnError( "Gallery has been disabled" );
		}
		
		/* Libs */
		define( 'GALLERY_LIBS', IPS_ROOT_PATH . '/applications_addon/ips/gallery/sources/libs/' );
		
		/* Gallery Object */
		require_once( GALLERY_LIBS . 'lib_gallery.php' );
		$this->registry->setClass( 'glib', new lib_gallery( $this->registry ) );
		
		/* Load the category object */
		require_once( GALLERY_LIBS . 'lib_categories.php' );
		$this->registry->setClass( 'category', new lib_categories( $this->registry ) );
		$this->registry->category->normalInit();
		
		$this->registry->glib->category = $this->registry->category;
		
		/* Get the album */
		$albumId = intval( $this->request['album'] );
		
		$this->album = $this->registry->glib->getAlbumInfo( $albumId );
		
		/* Check the album owner */
		if( $this->album['member_id'] != $this->memberData['member_id'] )
		{
			$this->_returnError( "You are not allowed to upload images to that album" );
		}
		
		/* Check album limits */
		if( $this->memberData['g_img_album_limit'] )
		{
			$count = $this->DB->buildAndFetch( array( 'select' => 'COUNT(*) AS total', 'from' => 'gallery_images', 'where' => "album_id={$albumId}" ) );

			if( $count['total'] >= $this->memberData['g_img_album_limit'] )
			{
				$this->_returnError( "You can not upload any more images to that album" );
			}
		}
		
		if( empty( $this->request['caption'] ) )
		{
			$this->request['caption'] = IPSText::parseCleanValue( $_FILES['image']['name'] );
		}
		
		/* Get upload settings */
		$settings = $this->getUploadSettings();
		
		/* Process the file */
		$new_file_info 	= $this->registry->glib->processUploadedFile( 'image', $settings['thumb'], $settings['watermark'], $settings['container'], $settings['allow_media'], $settings['allow_images'] );
		
		//-------------------------------------------------------------
		// Insert information into the database
		//-------------------------------------------------------------

		$insert = array( 	'member_id'			=> $this->memberData['member_id'],
							'category_id'		=> 0,
							'album_id'			=> $albumId,
							'caption'			=> $this->request['caption'],
							'description'		=> $this->request['description'],
							'approved'			=> $settings['approve'],
							'thumbnail'			=> $settings['thumbnail'],
							'views'				=> 0,
							'comments'			=> 0,
							'idate'				=> time(),
							'ratings_total'		=> 0,
							'ratings_count'		=> 0,
							'caption_seo'		=> IPSText::makeSeoTitle( $this->request['caption'] ),
							'image_notes'		=> '',
						);

		$insert = array_merge( $insert, $new_file_info );

		//-------------------------------------------------------------
		// Check copyright data
		//-------------------------------------------------------------

		if( $this->settings['gallery_allow_usercopyright'] != 'disabled' )
		{
			$insert['copyright'] = $this->settings['gallery_allow_usercopyright'] == '1' ? ( $this->request['copyright'] ? $this->request['copyright'] : $this->settings['gallery_copyright_default'] ) : $this->settings['gallery_copyright_default'];
		}	

		//-------------------------------------------------------
		// Do the insert
		//-------------------------------------------------------

		$this->DB->insert( 'gallery_images', $insert );
		
		if( ! $this->memberData['has_gallery'] )
		{
			$this->DB->update( 'members', array( 'has_gallery' => 1 ), "member_id={$this->memberData['member_id']}" );
		}

		$pid = $this->DB->getInsertId();
		
		if( $this->request['notify'] )
		{
			$this->DB->insert( 'gallery_subscriptions', array(
																	'sub_mid'	=> $this->memberData['member_id'],
																	'sub_type'	=> 'image',
																	'sub_toid'	=> $pid,
																	'sub_added'	=> time()
																)
								);
		}
		
		//------------------------------------------------------------
		// Need to update the parents, if they exist and this is a category
		//------------------------------------------------------------

		$this->registry->glib->reSyncEverything( $insert['category_id'], $insert['album_id'] );
		
		if( $insert['approved'] )
		{
			require_once( GALLERY_LIBS . 'lib_notifications.php' );
			$notify 			= new lib_notifications( $this->registry );
			$notify->category	= $this->category;
			
			if( $insert['album_id'] )
			{
				$notify->sendAlbumNotifications( $insert['album_id'] );
			}
			else
			{
				$notify->sendCatNotifications( $insert['category_id'] );
			}
		}
		
		exit();
	}
	
	/**
	 * Attempt to post a user status update
	 *
	 * @return	string		XML
	 */
	protected function _handlePostStatus()
	{
		/* Check the form hash */
		if( $this->member->form_hash != $this->request['form_hash'] )
		{
			$this->_returnError( "Invalid Request" );
		}
		
		/* INIT */
		$smallSpace  = intval( $this->request['smallSpace'] );
		$su_Twitter  = intval( $this->request['su_twitter'] );
		$su_Facebook = intval( $this->request['su_facebook'] );
		
		/* Load status class */
		if ( ! $this->registry->isClassLoaded( 'memberStatus' ) )
		{
			require_once( IPS_ROOT_PATH . 'sources/classes/member/status.php' );
			$this->registry->setClass( 'memberStatus', new memberStatus( ipsRegistry::instance() ) );
		}
		
		require_once( IPS_KERNEL_PATH . 'classAjax.php' );
		$this->ajax = new classAjax();
		
		/* Got content? */
		if( !trim( $this->ajax->convertAndMakeSafe( $_POST['content'] ) ) )
		{
			$this->returnJsonError( $this->lang->words['no_status_sent'] );
		}
		
		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Content */
		$this->registry->getClass('memberStatus')->setContent( trim( $this->ajax->convertAndMakeSafe( $_POST['content'] ) ) );
		
		/* Set post outs */
		$this->registry->getClass('memberStatus')->setExternalUpdates( array( 'twitter' => $su_Twitter, 'facebook' => $su_Facebook ) );
		
		/* Set creator */
		$this->registry->getClass('memberStatus')->setCreator( 'ipbmobiphone' );
							
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canCreate() )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Update */
		$newStatus = $this->registry->getClass('memberStatus')->create();
		
		/* Now grab the reply and return it */
		$new = $this->registry->getClass('output')->getTemplate('profile')->statusUpdates( $this->registry->getClass('memberStatus')->fetch( $this->memberData['member_id'], array( 'member_id' => $this->memberData['member_id'], 'sort_dir' => 'desc', 'limit' => 1 ) ), $smallSpace );
		exit;
	}
	
	/**
	 * Send an error about the selected api
	 *
	 * @return	string		XML
	 */
	protected function _invalidApi()
	{
		$this->_returnError( "Invalid API Request" );
	}
	
	/**
	 * Sends an error message in xml
	 *
	 * @param	string	$msg
	 * @return	void
	 */
	protected function _returnError( $msg )
	{
		/* XML Parser */
		require_once( IPS_KERNEL_PATH . 'classXML.php' );
		$xml = new classXML( 'utf-8' );
		$xml->newXMLDocument();
		
		/* Build Document */
		$xml->addElement( 'forum' );
		$xml->addElementAsRecord( 'forum', 'error', array( 'msg' => $msg ) );

		/* Output */
		echo $xml->fetchDocument();
		exit();
	}
	
	/**
	 * Sends forum data in xml format
	 *
	 * @param	array	$dataArray
	 * @return	void
	 */
	protected function _returnXml( $dataArray )
	{
		/* XML Parser */
		require_once( IPS_KERNEL_PATH . 'classXML.php' );
		$xml = new classXML( 'utf-8' );
		$xml->newXMLDocument();
		
		/* Build Document */
		$xml->addElement( 'forum' );
		$xml->addElementAsRecord( 'forum', 'capabilites', array( 'gallery'			=> $dataArray['gallery'] ) );
		$xml->addElementAsRecord( 'forum', 'capabilites', array( 'facebook'			=> $dataArray['facebook'] ) );
		$xml->addElementAsRecord( 'forum', 'capabilites', array( 'twitter'			=> $dataArray['twitter'] ) );
		$xml->addElementAsRecord( 'forum', 'capabilites', array( 'status'			=> $dataArray['status'] ) );
		$xml->addElementAsRecord( 'forum', 'capabilites', array( 'notifications'	=> $dataArray['notifications'] ) );
		$xml->addElement( 'albums', 'forum' );
		
		$xml->addElementAsRecord( 'forum', 'security', array( 'form_hash' => $dataArray['form_hash'] ) );
		
		/* Loop through albums */
		if( is_array( $dataArray['albums'] ) && count( $dataArray['albums'] ) )
		{
			foreach( $dataArray['albums'] as $r )
			{
				$xml->addElementAsRecord( 'albums', array( 'album' ), array( 'id' => $r['id'], 'name' => $r['name'] ) );
			}
		}
		
		/* Output */
		echo $xml->fetchDocument();
		exit();
	}
	
	/**
	 * Returns settings used for uploading images
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function getUploadSettings()
	{
		//-----------------------------------
		// Category Settings
		//-----------------------------------
		
		if( $this->cat )
		{
			$thumb     = ( $this->cat['thumbnail'] && $this->settings['gallery_create_thumbs'] ) ? 1 : 0;
			$watermark = $this->cat['watermark_images'];

			if ( !$this->memberData['g_movies'] OR !$this->cat['allow_movies'] )  
        	{
        		$allow_media = 0;
        	}
        	else
        	{
        		$allow_media = 1;
        	}

			$container      = $this->cat['id'];
			$html           = ( $this->cat['allow_html'] AND $this->memberData['g_dohtml'] ) ? 1 : 0 ;
			$approve_images = $this->cat['approve_images'] ? 0 : 1;
			$code           = $this->cat['allow_ibfcode'];
			$allow_images	= $this->cat['allow_images'];
		}
		
		//-----------------------------------
		// Album Settings
		//-----------------------------------
		
		else
		{
			$cat			= $this->registry->category->cat_lookup[ $this->album['category_id'] ];

			$thumb          = ( $cat['thumbnail'] && $this->settings['gallery_create_thumbs'] ) ? 1 : 0;
			$watermark      = ( $cat['watermark_images'] && $this->settings['gallery_watermark_path'] ) ? 1 : 0;
			$allow_media 	= ( $cat['allow_movies'] && $this->memberData['g_movies'] ) ? 1 : 0;
			$container      = $this->album['id'];
			$html           = ( $cat['allow_html'] && $this->memberData['g_dohtml'] ) ? 1 : 0 ;
			$approve_images = ( $cat['approve_images'] ) ? 0 : 1;
			$code           = ( $cat['allow_ibfcode'] ) ? 1 : 0;
			$allow_images	= $cat['allow_images'] ? 1 : 0;
		}

		return array( 	'thumb'     	=> $thumb,
						'watermark' 	=> $watermark,
						'html'      	=> $html,
						'code'			=> $code,
						'allow_media' 	=> $allow_media,
						'approve'   	=> $approve_images,
						'container' 	=> $container,
						'allow_images'	=> $allow_images,
					);
	}
}

/* Setup the registry */
$registry = ipsRegistry::instance();
$registry->init();

/* Handle the request */
$apiRequest = new mobileApiRequest( $registry );
$apiRequest->dispatch();

exit();