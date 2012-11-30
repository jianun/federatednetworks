<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Configure default notification options
 * Last Updated: $Date: 2010-06-17 21:29:47 -0400 (Thu, 17 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6553 $
 *
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_members_members_notifications extends ipsCommand
{
	/**
	 * Skin object
	 *
	 * @access	private
	 * @var		object			Skin templates
	 */	
	private $html;
	
	/**
	 * Shortcut for url
	 *
	 * @access	private
	 * @var		string			URL shortcut
	 */
	private $form_code;
	
	/**
	 * Shortcut for url (javascript)
	 *
	 * @access	private
	 * @var		string			JS URL shortcut
	 */
	private $form_code_js;
	
	/**
	 * Notifications library
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $notifyLibrary;

	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Load skin
		//-----------------------------------------
		
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_notifications' );
		
		//-----------------------------------------
		// Set up stuff
		//-----------------------------------------
		
		$this->form_code	= $this->html->form_code	= 'module=members&amp;section=notifications';
		$this->form_code_js	= $this->html->form_code_js	= 'module=members&section=notifications';
		
		//-----------------------------------------
		// Load lang
		//-----------------------------------------
				
		$this->registry->class_localization->loadLanguageFile( array( 'admin_member' ) );
		
		//-----------------------------------------
		// Permissions config
		//-----------------------------------------
		
		$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'configure_notifications' );
		
		//-----------------------------------------
		// Notifications library
		//-----------------------------------------
		
		$classToLoad			= IPSLib::loadLibrary( IPS_ROOT_PATH . '/sources/classes/member/notifications.php', 'notifications' );
		$this->notifyLibrary	= new $classToLoad( $this->registry );
		
		//-----------------------------------------
		// What to do...
		//-----------------------------------------
		
		switch( $this->request['do'] )
		{				
			case 'save':
				$this->saveDefaults();
			break;

			default:
			case 'show':
				$this->showDefaults();
			break;			
		}
		
		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------
		
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();		
	}
	
	/**
	 * Show form to configure notification default options
	 *
	 * @access	public
	 * @return	void
	 */
	public function showDefaults()
	{
		$_configOptions	= $this->notifyLibrary->getNotificationData();
		$_notifyConfig	= $this->notifyLibrary->getDefaultNotificationConfig();

		$this->registry->output->html .= $this->html->showConfigurationOptions( $_configOptions, $_notifyConfig );
	}

	/**
	 * Save default notification configuration
	 *
	 * @access	public
	 * @return	void
	 */
	public function saveDefaults()
	{
		$_configOptions		= $this->notifyLibrary->getNotificationData();
		$_notifyConfig		= $this->notifyLibrary->getDefaultNotificationConfig();
		$_noPrivateMessage	= array( 'new_private_message', 'reply_private_message' );

		foreach( $_configOptions as $option )
		{
			$_notifyConfig[ $option['key'] ]						= array();
			$_notifyConfig[ $option['key'] ]['selected']			= ( is_array($this->request['default_' . $option['key'] ]) AND count($this->request['default_' . $option['key'] ]) ) ? $this->request['default_' . $option['key'] ] : array();
			$_notifyConfig[ $option['key'] ]['disabled']			= ( is_array($this->request['disabled_' . $option['key'] ]) AND count($this->request['disabled_' . $option['key'] ]) ) ? $this->request['disabled_' . $option['key'] ] : array();
			$_notifyConfig[ $option['key'] ]['disable_override']	= intval($this->request['disable_override_' . $option['key'] ]);
			
			/**
			 * @link	http://community.invisionpower.com/tracker/issue-23453-notification-defaults/
			 */
			if( in_array( $option['key'], $_noPrivateMessage ) AND in_array( 'pm', $_notifyConfig[ $option['key'] ]['selected'] ) )
			{
				$_newSelected	= array();
				
				foreach( $_notifyConfig[ $option['key'] ]['selected'] as $_v )
				{
					if( $_v != 'pm' )
					{
						$_newSelected[]	 = $_v;
					}
				}
				
				$_notifyConfig[ $option['key'] ]['selected']	= $_newSelected;
			}
		}

		$this->notifyLibrary->saveNotificationConfig( $_notifyConfig );
		
		$this->registry->output->global_message = $this->lang->words['notificationconfig_saved'];
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'] . $this->form_code );
	}
}