<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Portal plugin: online users
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ppi_online_users extends public_portal_portal_portal 
{
	/**
	 * Initialize module
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
 	{
 	}
 	
	/**
	 * Show the online users
	 *
	 * @access	public
	 * @return	string		HTML content to replace tag with
	 */
	public function online_users_show()
	{
		//-----------------------------------------
		// Get the users from the DB
		//-----------------------------------------
		
		require_once( IPSLib::getAppDir( 'forums', 'forums' ) . '/boards.php' );
		$boards	= new public_forums_forums_boards();
		$boards->makeRegistryShortcuts( $this->registry );
		
		$active				= $boards->getActiveUserDetails();
		$active['visitors']	= $active['GUESTS']  + $active['ANON'];
		$active['members']	= $active['MEMBERS'];

 		return $this->registry->getClass('output')->getTemplate('portal')->onlineUsers( $active );
 	}

}