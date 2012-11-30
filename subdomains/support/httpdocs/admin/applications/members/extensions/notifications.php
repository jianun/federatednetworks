<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Define the core notification types
 * Last Updated: $Date: 2010-04-16 21:25:51 -0400 (Fri, 16 Apr 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 6142 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * Notification types
 */

class members_notifications
{
	public function getConfiguration()
	{
		/**
		 * Notification types - Needs to be a method so when require_once is used, $_NOTIFY isn't empty
		 */
		$_NOTIFY	= array(
							array( 'key' => 'profile_comment', 'default' => array( 'pm' ), 'disabled' => array(), 'icon' => 'notify_profilecomment' ),
							array( 'key' => 'profile_comment_pending', 'default' => array( 'pm' ), 'disabled' => array(), 'icon' => 'notify_profilecomment' ),
							array( 'key' => 'friend_request', 'default' => array( 'pm' ), 'disabled' => array(), 'icon' => 'notify_friendrequest' ),
							array( 'key' => 'friend_request_pending', 'default' => array( 'pm' ), 'disabled' => array(), 'icon' => 'notify_friendrequest' ),
							array( 'key' => 'friend_request_approve', 'default' => array( 'pm' ), 'disabled' => array(), 'icon' => 'notify_friendrequest' ),
							array( 'key' => 'new_private_message', 'default' => array( 'email', 'inline' ), 'disabled' => array( 'pm' ), 'icon' => 'notify_pm' ),
							array( 'key' => 'reply_private_message', 'default' => array( 'email', 'inline' ), 'disabled' => array( 'pm' ), 'icon' => 'notify_pm' ),
							array( 'key' => 'invite_private_message', 'default' => array( 'email', 'inline' ), 'disabled' => array( 'pm' ), 'icon' => 'notify_pm' ),
							array( 'key' => 'reply_your_status', 'default' => array(), 'disabled' => array(), 'icon' => 'notify_statusreply' ),
							array( 'key' => 'reply_any_status', 'default' => array(), 'disabled' => array(), 'icon' => 'notify_statusreply' ),
							array( 'key' => 'friend_status_update', 'default' => array(), 'disabled' => array(), 'icon' => 'notify_statusreply' ),
							);
							
		return $_NOTIFY;
	}
}

