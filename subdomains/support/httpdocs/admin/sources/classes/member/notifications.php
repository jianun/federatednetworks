<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Notification library
 * Last Updated: $Date: 2010-07-10 01:01:06 -0400 (Sat, 10 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $ (Original: bfarber)
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		Thursday Jan 7, 2010
 * @version		$Revision: 6628 $
 *
 * @todo 		Need to push Downloads, Blog and Gallery into new system
 */

/**
 * Notifications class.
 * Sends notifications to member(s) based on their configured notification options.
 */
class notifications
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
	protected $memberData;
	protected $cache;
	protected $caches;
	/**#@-*/
	
	/**
	 * Member data
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $_member				= array();
	
	/**
	 * From member data (usually $this->memberData)
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $_from				= array();

	/**
	 * Notification definitions
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $_notificationData	= array();
	
	/**
	 * Notification key
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $_notificationKey		= '';
	
	/**
	 * Notification text
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $_notificationText	= '';

	/**
	 * Notification title
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $_notificationTitle	= '';

	/**
	 * Notification URL
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $_notificationUrl		= '';

	/**
	 * CONSTRUCTOR
	 *
	 * @param  blog_show $class
	 * @return void
	 **/
	public function __construct( ipsRegistry $registry )
	{
		/* Make registry objects */
		$this->registry		=  $registry;
		$this->DB			=  $this->registry->DB();
		$this->settings		=& $this->registry->fetchSettings();
		$this->request		=& $this->registry->fetchRequest();
		$this->lang			=  $this->registry->getClass('class_localization');
		$this->member		=  $this->registry->member();
		$this->memberData	=& $this->registry->member()->fetchMemberData();
		$this->cache		=  $this->registry->cache();
		$this->caches		=& $this->registry->cache()->fetchCaches();
		
		//-----------------------------------------
		// Set default
		//-----------------------------------------
		
		$this->_from		= $this->memberData;
	}
	
	/**
	 * Get HTML for board index hook
	 *
	 * @access	public
	 * @return	string
	 */
	public function getBoardIndexHook()
	{
		$_notifications	= $this->fetchUnreadNotifications(10, 'notify_sent', 'DESC');
		
		//-----------------------------------------
		// If none are unread, no HTML
		//-----------------------------------------
		
		if( !count($_notifications) )
		{
			return '';
		}
		
		return $this->registry->output->getTemplate('boards')->hookUnreadNotifications( $_notifications );
	}

	/**
	 * Set Member
	 *
	 * @access	public
	 * @param	mixed		Member id (int) or member data (array)
	 * @return	void
	 */
	public function setMember( $member )
	{
		if( is_int($member) )
		{
			$member	= IPSMember::load( $member );
		}
		
		$this->_member	= $member;
	}
	
	/**
	 * Set member message should come 'from'
	 *
	 * @access	public
	 * @param	mixed		Member id (int) or member data (array)
	 * @return	void
	 */
	public function setFrom( $member )
	{
		if( is_int($member) )
		{
			$member	= IPSMember::load( $member );
		}
		
		$this->_from	= $member;
	}
	
	/**
	 * Set notification type key
	 *
	 * @access	public
	 * @param	string		Notification type key
	 * @return	void
	 */
	public function setNotificationKey( $key )
	{
		$this->_notificationKey		= $key;
	}
	
	/**
	 * Set notification URL
	 *
	 * @access	public
	 * @param	string		Notification URL
	 * @return	void
	 */
	public function setNotificationUrl( $url )
	{
		$this->_notificationUrl		= $url;
	}
	
	/**
	 * Set notification text
	 *
	 * @access	public
	 * @param	string		Text
	 * @return	void
	 */
	public function setNotificationText( $text )
	{
		$this->_notificationText	= $text;
	}
	
	/**
	 * Set notification title
	 *
	 * @access	public
	 * @param	string		Title
	 * @return	void
	 */
	public function setNotificationTitle( $title )
	{
		$this->_notificationTitle	= $title;
	}
	
	/**
	 * Get all notification types
	 *
	 * @access	public
	 * @return	array
	 */
	public function getNotificationKeys()
	{
		$data	= $this->getNotificationData();
		
		return array_keys( $data );
	}
	
	/**
	 * Get notification config file data
	 *
	 * @access	public
	 * @param	bool	If true, will check show_callback to see if user has permission
	 * @return	array
	 */
	public function getNotificationData( $checkCallbacks=FALSE )
	{
		//-----------------------------------------
		// Already stored the data?
		//-----------------------------------------
		
		if( count($this->_notificationData) )
		{
			return $this->_notificationData;
		}
		
		//-----------------------------------------
		// Get for each application
		//-----------------------------------------
		
		foreach( ipsRegistry::$applications as $app_dir => $application )
		{
			if( file_exists( IPSLib::getAppDir( $app_dir ) . '/extensions/notifications.php' ) )
			{
				require_once( IPSLib::getAppDir( $app_dir ) . '/extensions/notifications.php' );
				
				$className = $app_dir . '_notifications';
				
				if ( class_exists( $className ) )
				{
					$class = new $className();
					$class->memberData = ipsRegistry::member()->fetchMemberData();
						
					$_NOTIFY = $class->getConfiguration();
						
					if ( $checkCallbacks )
					{
						foreach ( $_NOTIFY as $n )
						{
							if ( $n['show_callback'] and method_exists( $class, $n['key'] ) )
							{
								if ( $class->$n['key']() )
								{
									$this->_notificationData[] = $n;
								}
							}
							else
							{
								$this->_notificationData[] = $n;
							}
						}
					}
					else
					{
						$this->_notificationData    = ( is_array( $this->_notificationData ) ) ? $this->_notificationData : array();
						$this->_notificationData	= array_merge( $this->_notificationData, $_NOTIFY );
					}
				}
			}
		}
		
		return $this->_notificationData;
	}
	
	/**
	 * Format the notification data as if it were configured
	 *
	 * @access	public
	 * @return	array
	 */
	public function formatNotificationData()
	{
		$_data		= $this->getNotificationData();
		$_return	= array();

		foreach( $_data as $data )
		{
			$_return[ $data['key'] ]						= array();
			$_return[ $data['key'] ]['selected']			= $data['default'];
			$_return[ $data['key'] ]['disabled']			= $data['disabled'];
			$_return[ $data['key'] ]['disable_override']	= 0;
		}
		
		return $_return;
	}
	
	/**
	 * Get the ACP-set notification configuration
	 *
	 * @access	public
	 * @return	array
	 */
	public function getDefaultNotificationConfig()
	{
		return $this->cache->getCache('notifications') ? $this->cache->getCache('notifications') : $this->formatNotificationData();
	}
	
	/**
	 * Save the ACP-set notification configuration
	 *
	 * @access	public
	 * @param	array 	Notification configuration
	 * @return	void
	 */
	public function saveNotificationConfig( $config )
	{
		$this->cache->setCache( 'notifications', $config, array( 'array' => 1 ) );
	}
	
	/**
	 * Get the member's notification configuration
	 *
	 * @access	public
	 * @return	array
	 * @link	http://community.invisionpower.com/tracker/issue-23529-notification-defaults-dont-apply/
	 * @link	http://community.invisionpower.com/tracker/issue-23663-notification-issues/
	 */
	public function getMemberNotificationConfig()
	{
		$_cache	= IPSMember::unpackMemberCache( $this->_member['members_cache'] );
		
		if( $_cache['notifications'] )
		{
			$savedTypes		= array_keys( $_cache['notifications'] );
			$_default		= $this->getDefaultNotificationConfig();
			$defaultTypes	= array_keys( $_default );
			$missingTypes	= array_diff( $savedTypes, $defaultTypes );
			
			//-----------------------------------------
			// Grab any missing types
			//-----------------------------------------
			
			foreach( $missingTypes as $_type )
			{
				$_cache['notifications'][ $_type ]	= $_default[ $_type ]['selected'];
			}
			
			//-----------------------------------------
			// Make changes if admin has disallowed override
			// since we saved our config
			//-----------------------------------------

			foreach( $_default as $k => $sub )
			{
				if( $sub['disable_override'] )
				{
					$_cache['notifications'][ $k ]['selected']	= $sub['selected'];
				}
				else if( $sub['disabled'] )
				{
					$_newSelection	= array();
					
					if( is_array($_cache['notifications'][ $k ]['selected']) AND count($_cache['notifications'][ $k ]['selected']) )
					{
						foreach( $_cache['notifications'][ $k ]['selected'] as $_thisType )
						{
							if( !in_array( $_thisType, $sub['disabled'] ) )
							{
								$_newSelection[]	= $_thisType;
							}
						}
					}

					$_cache['notifications'][ $k ]['selected']	= $_newSelection;
				}
			}

			return $_cache['notifications'];
		}
		else
		{
			return $this->getDefaultNotificationConfig();
		}
	}
	
	/**
	 * Send notification
	 *
	 * @access	public
	 * @return	bool
	 * @throws	NO_MEMBER_ID, NO_NOTIFY_KEY, BAD_NOTIFY_KEY
	 */
	public function sendNotification()
	{
		if( !$this->_member['member_id'] )
		{
			throw new Exception( 'NO_MEMBER_ID' );
		}
		
		if( !$this->_notificationKey )
		{
			throw new Exception( 'NO_NOTIFY_KEY' );
		}
		
		$_config	= $this->getMemberNotificationConfig();

		if( !$_config[ $this->_notificationKey ] )
		{
			throw new Exception( 'BAD_NOTIFY_KEY' );
		}

		//-----------------------------------------
		// Send appropriate notifications.
		// We can have more than one notification method for each
		//	notification type.
		//-----------------------------------------

		if( is_array($_config[ $this->_notificationKey ]) AND count($_config[ $this->_notificationKey ]) )
		{
			foreach( $_config[ $this->_notificationKey ]['selected'] as $_type )
			{
				switch( $_type )
				{
					case 'pm':
						$this->_sendPMNotification();
					break;
					
					case 'email':
						$this->_sendEmailNotification();
					break;
					
					case 'inline':
						$this->_sendInlineNotification();
					break;
					
					case 'mobile':
						$this->_sendMobileNotification();
					break;
				}
			}
		}
	}
	
	/**
	 * Send a notification via mobile device
	 *
	 * @access	protected
	 * @return	mixed		True, or can output an error
	 */
	protected function _sendMobileNotification()
	{
		/* Just save the notification, a task will handle it later */
		if( IPSLib::canReceiveMobileNotifications( $this->_member ) && $this->_member['ips_mobile_token'] )
		{
			$this->DB->insert( 'mobile_notifications', array(
																'notify_title'	=> strip_tags( $this->_notificationTitle ),
																'notify_date'	=> time(),
																'member_id'		=> $this->_member['member_id']
															)
							);
		}
	}
	
	/**
	 * Send a notification via private conversation
	 *
	 * @access	protected
	 * @return	mixed		True, or can output an error
	 */
	protected function _sendPMNotification()
	{
		try
		{
			$_classToLoad	= IPSLib::loadLibrary( IPSLib::getAppDir( 'members' ) . '/sources/classes/messaging/messengerFunctions.php', 'messengerFunctions', 'members' );
			
			$_messenger		= new $_classToLoad( $this->registry );
		 	$_messenger->sendNewPersonalTopic( intval( $this->_member['member_id'] ), 
											intval( $this->_from['member_id'] ), 
											array(), 
											strip_tags($this->_notificationTitle), 
											IPSText::getTextClass('editor')->method == 'rte' ? nl2br( $this->_notificationText ) : $this->_notificationText,
											array( 'origMsgID'			=> 0,
													'fromMsgID'			=> 0,
													'postKey'			=> md5(microtime()),
													'trackMsg'			=> 0,
													'addToSentFolder'	=> 0,
													'hideCCUser'		=> 0,
													'forcePm'			=> 1,
													'isSystem'          => true
												)
											);
		}
		catch( Exception $error )
		{
			$msg		= $error->getMessage();
			
			if( $msg != 'CANT_SEND_TO_SELF' )
			{
				if ( strstr( $msg, 'BBCODE_' ) )
			    {
					$msg = str_replace( 'BBCODE_', '', $msg );

					$this->registry->output->showError( $msg, 10149 );
				}
				else if ( isset($this->lang->words[ 'err_' . $msg ]) )
				{
					$this->lang->words[ 'err_' . $msg ] = $this->lang->words[ 'err_' . $msg ];
					$this->lang->words[ 'err_' . $msg ] = str_replace( '#NAMES#'	, implode( ",", $_messenger->exceptionData )	, $this->lang->words[ 'err_' . $msg ] );
					$this->lang->words[ 'err_' . $msg ] = str_replace( '#TONAME#'	, $this->_member['members_display_name']		, $this->lang->words[ 'err_' . $msg ] );
					$this->lang->words[ 'err_' . $msg ] = str_replace( '#FROMNAME#'	, $this->_from['members_display_name']			, $this->lang->words[ 'err_' . $msg ] );
					
					$this->registry->output->showError( 'err_' . $msg, 10150 );
				}
				else
				{
					$_msgString = $this->lang->words['err_UNKNOWN'] . ' ' . $msg;
					
					$this->registry->output->showError( 'err_UNKNOWN', 10151 );
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Send a notification via email
	 *
	 * @access	protected
	 * @return	bool
	 */
	protected function _sendEmailNotification()
	{
		IPSText::getTextClass( 'email' )->message	= $this->_notificationText;
		IPSText::getTextClass( 'email' )->subject	= strip_tags( $this->_notificationTitle );
		IPSText::getTextClass( 'email' )->to		= $this->_member['email'];
		IPSText::getTextClass( 'email' )->from		= $this->settings['email_out'];
		
		//-----------------------------------------
		// Send immediately
		//-----------------------------------------
		
		//IPSText::getTextClass( 'email' )->sendMail();
		
		//-----------------------------------------
		// Send delayed
		//-----------------------------------------
		
		$this->DB->insert( 'mail_queue', array( 'mail_to' => IPSText::getTextClass( 'email' )->to, 'mail_from' => IPSText::getTextClass( 'email' )->from, 'mail_date' => time(), 'mail_subject' => IPSText::getTextClass('email')->subject, 'mail_content' => IPSText::getTextClass('email')->message ) );

		$cache					= $this->cache->getCache('systemvars');
		$cache['mail_queue']	+= 1;
		$this->cache->setCache( 'systemvars', $cache, array( 'array' => 1, 'donow' => 1 ) );
		
		return true;
	}
	
	/**
	 * Send an inline notification
	 *
	 * @access	protected
	 * @return	bool
	 */
	protected function _sendInlineNotification()
	{
		//-----------------------------------------
		// First, make sure member doesn't have too many
		//-----------------------------------------
		
		$this->_truncateInlineNotifications();
		
		//-----------------------------------------
		// Insert new notification
		//-----------------------------------------

		$_insert	= array(
							'notify_to_id'		=> $this->_member['member_id'],
							'notify_from_id'	=> intval($this->_from['member_id']),
							'notify_sent'		=> time(),
							'notify_read'		=> 0,
							'notify_title'		=> $this->_notificationTitle,
							'notify_text'		=> $this->_notificationText,
							'notify_type_key'	=> $this->_notificationKey,
							'notify_url'		=> $this->_notificationUrl,
							);

		$this->DB->insert( 'inline_notifications', $_insert );
		
		//-----------------------------------------
		// Update member record
		//-----------------------------------------
		
		$this->DB->update( 'members', 'notification_cnt=notification_cnt+1, msg_show_notification=1', 'member_id=' . $this->_member['member_id'], true, true );

		return true;
	}
	
	/**
	 * Clear out old notifications if there's a limit to how many member can have
	 *
	 * @access	public
	 * @return	void
	 */
	public function _truncateInlineNotifications()
	{
		//-----------------------------------------
		// Determine member's limit first
		//-----------------------------------------
		
		$groups	= array( $this->_member['member_group_id'] );
		
		if( $this->_member['mgroup_others'] )
		{
			$_others	= IPSText::cleanPermString( $this->_member['mgroup_others'] );
			$groups		= ( is_array( $groups ) AND is_array( $_others ) ) ? array_merge( $_others, $groups ) : array();
		}
		
		//-----------------------------------------
		// 0 is best, otherwise higher is better
		//-----------------------------------------
		
		$_limit			= 0;
		
		foreach( $groups as $_group )
		{
			$_thisLimit	= $this->caches['group_cache'][ $_group ]['g_max_notifications'];
			
			if( !$_thisLimit )
			{
				$_limit	= 0;
				break;
			}
			else if( $_thisLimit > $_limit )
			{
				$_limit	= $_thisLimit;
			}
		}

		//-----------------------------------------
		// We have a limit
		//-----------------------------------------

		if( $_limit )
		{
			//-----------------------------------------
			// Get current count
			//-----------------------------------------
			
			$_count	= $this->DB->buildAndFetch( array( 'select' => 'COUNT(*) as total', 'from' => 'inline_notifications', 'where' => 'notify_to_id=' . $this->_member['member_id'] ) );
			
			//-----------------------------------------
			// At limit?
			// We use >= because this is run immediately before
			//	we add a new notification, so if we are at limit
			//	we still need to remove 1
			//-----------------------------------------

			if( $_count['total'] >= $_limit )
			{
				$_toDelete	= ( $_count['total'] + 1 ) - $_limit;
				
				//-----------------------------------------
				// Yes, delete method supports order by and limit
				//-----------------------------------------
				
				$this->DB->delete( 'inline_notifications', 'notify_to_id=' . $this->_member['member_id'], 'notify_sent ASC', array( $_toDelete ) );
			}
		}

		return;
	}
	
	/**
	 * Rebuild a member's unread notification count
	 *
	 * @access	public
	 * @return	void
	 */
	public function rebuildUnreadCount()
	{
		$count	= $this->DB->buildAndFetch( array( 'select' => 'count(*) as total', 'from' => 'inline_notifications', 'where' => 'notify_to_id=' . $this->_member['member_id'] . ' AND notify_read=0' ) );
		
		$this->DB->update( 'members', array( 'notification_cnt' => $count['total'] ), 'member_id=' . $this->_member['member_id'] );
	}
	
	/**
	 * Fetch new PM notification
	 *
	 * @access	public
	 * @param	int			Number if items to limit
	 * @param	string		Sort column
	 * @param	string		Sort order
	 * @param	bool		Only get unread notifications
	 * @param	bool		Run text through preDisplayParse
	 * @return 	array 		Unread notifications
	 */
	public function fetchUnreadNotifications( $limit=0, $sortKey='notify_sent', $sortOrder='desc', $unread=true, $parseText=false )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$return   = array();
		$limit    = ( $limit ) ? array( 0, intval( $limit ) ) : array( 0, 500 );
		
		//-----------------------------------------
		// Fetch unread notifications
		//-----------------------------------------
		
		$this->DB->build( array( 'select'   => '*',
								 'from'     => 'inline_notifications',
								 'where'    => 'notify_to_id=' . $this->_member['member_id'] . ( $unread ? ' AND notify_read=0' : '' ),
								 'order'    => $sortKey . ' ' . $sortOrder,
								 'limit'    => $limit ) );
								 
		$outer	= $this->DB->execute();
		
		while( $row = $this->DB->fetch($outer) )
		{
			/* As the email template parser makes an attempt to reparse 'safe' HTML, we need to make it safe here */
			$row['notify_text'] = IPSText::htmlspecialchars( $row['notify_text'] );
			
	 		IPSText::getTextClass('bbcode')->parse_smilies				= 1;
	 		IPSText::getTextClass('bbcode')->parse_nl2br				= 1;
	 		IPSText::getTextClass('bbcode')->parse_html					= 0;
	 		IPSText::getTextClass('bbcode')->parse_bbcode				= 1;
	 		IPSText::getTextClass('bbcode')->parsing_section			= 'global';
	 		
	 		if( $parseText )
	 		{
	 			$row['notify_text'] = IPSText::getTextClass('bbcode')->preDisplayParse( nl2br( $row['notify_text'] ) );
 			}
	 		
	 		$row['notify_icon']	= $this->getNotificationIcon( $row['notify_type_key'] );
 			 			 			
			$return[ $row['notify_sent'] . '.' . $row['notify_id'] ] = $row;
		}
		
		/* Got anything? */
		if ( ! count( $return ) )
		{
			return array();
		}

		/* Reverse sort it (latest first) */
		// Removed (bfarber) - this negates the ability to custom sort the returned notifications by passing in 2nd/3rd function
		// params, so I'm commenting out for now
		//krsort( $return );
		
		/* Return 'em */
		return $return;
	}
	
	/**
	 * Get notification icon
	 *
	 * @access	public
	 * @param	string		Notification key
	 * @return	string		Notification icon
	 */
	public function getNotificationIcon( $key )
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------

		if( !$key )
		{
			return '';
		}
		
		//-----------------------------------------
		// Get data
		//-----------------------------------------
		
		$this->_notificationData = $this->getNotificationData();
		
		//-----------------------------------------
		// Now look for key and return icon
		//-----------------------------------------
		
		foreach( $this->_notificationData as $data )
		{
			if( $data['key'] == $key )
			{
				return $data['icon'];
			}
		}
		
		return '';
	}
}