<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Define the core notification types
 * Last Updated: $Date: 2010-04-08 16:37:04 -0400 (Thu, 08 Apr 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 6088 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}



class core_notifications
{
	public function getConfiguration()
	{
		/**
		 * Notification types - Needs to be a method so when require_once is used, $_NOTIFY isn't empty
		 */
		$_NOTIFY	= array(
							// Future...
							// array( 'key' => 'reputation_received', 'default' => array( 'inline' ), 'disabled' => array() ),
							array( 'key' => 'report_center', 'default' => array( 'email' ), 'disabled' => array(), 'show_callback' => TRUE, 'icon' => 'notify_reportcenter' ),
							);
		return $_NOTIFY;
	}
	
	public function report_center()
	{
		return $this->memberData['access_report_center'];
	}
}