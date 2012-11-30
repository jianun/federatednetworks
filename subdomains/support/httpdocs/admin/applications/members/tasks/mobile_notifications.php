<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Task: Sends Mobile Notifications
 * Last Updated: $LastChangedDate$
 * </pre>
 *
 * @author 		$Author$
 * @copyright	(c) 2001 - 2010 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @version		$Rev$
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	/**
	 * Parent task manager class
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $class;

	/**
	 * This task data
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $task			= array();

	/**
	 * Prevent logging
	 *
	 * @access	protected
	 * @var		boolean
	 */
	protected $restrict_log	= false;
	
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
	protected $cache;
	/**#@-*/
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param 	object		ipsRegistry reference
	 * @param 	object		Parent task class
	 * @param	array 		This task data
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry, $class, $task )
	{
		/* Make registry objects */
		$this->registry	= $registry;
		$this->DB		= $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang		= $this->registry->getClass('class_localization');
		$this->member	= $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();

		$this->class	= $class;
		$this->task		= $task;
	}
	
	/**
	 * Run this task
	 *
	 * @access	public
	 * @return	void
	 */
	public function runTask()
	{
		/* INIT */
		$maxNotificationsToProcess	= 250;
		$licenseKey					= ipsRegistry::$settings['ipb_reg_number'];
		$forum						= urlencode( $this->settings['board_name'] );
		$domain 					= urlencode( ipsRegistry::$settings['board_url'] );
		$apiBaseURL					= "http://apn-server.invisionpower.com/index.php?api=addMessageToQueue&key={$licenseKey}&forum={$forum}&domain={$domain}";

		/* Get the file managemnet class */
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
		$query = new classFileManagement();
		$query->use_sockets = 1;
		
		/* Get waiting notifications */
		$this->DB->build( array(
								'select'		=> 'n.*',
								'from'			=> array( 'mobile_notifications' => 'n' ),
								'where'			=> 'n.notify_sent=0',
								'order'			=> 'n.notify_date ASC',
								'limit'			=> array( 0, $maxNotificationsToProcess ),
								'add_join'		=> array(
															array(
																	'select'	=> 'm.ips_mobile_token',
																	'from'		=> array( 'members' => 'm' ),
																	'where'		=> 'n.member_id=m.member_id',
																	'type'		=> 'left',
																)
														)
						)	);
		$this->DB->execute();
		
		$_sentIds = array();
		while( $r = $this->DB->fetch() )
		{
			/* VARS */
			$ipsToken	= $r['ips_mobile_token'];
			$message	= urlencode( strip_tags( $r['notify_title'] ) );
			
			if( ! $ipsToken || ! $message )
			{
				continue;
			}

			/* Query the api */
			$response = $query->getFileContents( "{$apiBaseURL}&ipsToken={$ipsToken}&message={$message}" );
			
			/* Save the ID */
			$_sentIds[] = $r['id'];
		}
		
		/* Update the table */
		if( count( $_sentIds ) )
		{
			$this->DB->update( 'mobile_notifications', array( 'notify_sent' => 1 ), 'id IN ('.implode( ',', $_sentIds ).')' );
		}

		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['task_mobileNotifications'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}