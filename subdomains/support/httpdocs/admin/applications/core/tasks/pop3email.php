<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Task: Incoming Email Handler - POP3
 * Last Updated: $LastChangedDate: 2010-06-25 07:37:32 -0400 (Fri, 25 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2010 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		25th June 2010
 * @version		$Rev: 6575 $
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
	
		//--------------------------------------
		// Init
		//--------------------------------------
		
		require_once( IPS_KERNEL_PATH . '/pop3class/pop3.php' );
		$this->pop3 = new pop3_class;
		
		$this->pop3->hostname							= $this->settings['pop3_server'];
		$this->pop3->port								= $this->settings['pop3_port'];
		$this->pop3->tls								= $this->settings['pop3_tls'];
		$this->pop3->realm							= '';
		$this->pop3->workstation						= '';
		$this->pop3->authentication_mechanism			= 'USER';
		$this->pop3->debug							= FALSE;
		$this->pop3->html_debug						= FALSE;
		$this->pop3->join_continuation_header_lines	= FALSE;
		
		$user					= $this->settings['pop3_user'];
		$password				= $this->settings['pop3_password'];
		$apop					= FALSE;
		
		//--------------------------------------
		// Connect and login
		//--------------------------------------
		
		$open = $this->pop3->Open();
		if ( $open != '' )
		{
			return;
		}
		
		$login = $this->pop3->Login( $user, $password, $apop );
		if ( $login != '' )
		{
			return;
		}
		
		//--------------------------------------
		// Any messages?
		//--------------------------------------
		
		$messages = NULL;
		$size = NULL;
		$this->pop3->Statistics( $messages, $size );
				
		if ( !$messages )
		{
			return;
		}
		
		//--------------------------------------
		// Well get them then!
		//--------------------------------------
		
		require_once ( IPS_KERNEL_PATH . 'classIncomingEmail.php' );
		
		$result = $this->pop3->ListMessages( '', TRUE );
				
		if ( is_array( $result ) and !empty( $result ) )
		{
			foreach ( $result as $id => $messageID )
			{
				$headers = NULL;
				$body = NULL;
				$getMessage = $this->RetrieveMessage( $id );
				if ( $getMessage === NULL )
				{
					continue;
				}
				
				$incomingEmail = new classIncomingEmail( $getMessage );
				$incomingEmail->route();
			}
		}
		
		//--------------------------------------
		// Log off
		//--------------------------------------
		
		$this->pop3->Close();
		
		//-----------------------------------------
		// Cleanup
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['task_pop3email'] );
		$this->class->unlockTask( $this->task );
	}
	
	protected function RetrieveMessage( $id )
	{
		if( $this->pop3->PutLine( "RETR {$id}" ) == 0 )
		{
			return NULL;
		}
		
		$response = $this->pop3->GetLine();
		if ( $response != "+OK message follows" )
		{
			return NULL;
		}
		
		$message = '';
		while ( TRUE == TRUE )
		{
			$line = $this->pop3->GetLine();
			if ( $line == '.' )
			{
				break;
			}
			$message .= $line . "\n";
		}
		
		return $message;
	}
	
}