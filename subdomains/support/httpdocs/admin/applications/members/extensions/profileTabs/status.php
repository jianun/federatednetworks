<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Profile Plugin Library
 * Last Updated: $Date: 2010-07-15 21:31:17 -0400 (Thu, 15 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 6661 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class profile_status extends profile_plugin_parent
{
	/**
	 * Feturn HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array() ) 
	{
		if ( ! is_array( $member ) OR ! count( $member ) )
		{
			return $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'err_no_status_to_show' );
		}
		
		/* Load status class */
		if ( ! $this->registry->isClassLoaded( 'memberStatus' ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/status.php', 'memberStatus' );
			$this->registry->setClass( 'memberStatus', new $classToLoad( ipsRegistry::instance() ) );
		}
		
		/* Fetch */
		$statuses = $this->registry->getClass('memberStatus')->fetch( $this->memberData, array( 'member_id' => $member['member_id'], 'limit' => 2 ) );
		
		/* Fetch actions */
		$actions = $this->registry->getClass('memberStatus')->fetchActions( $this->memberData, array( 'member_id' => $member['member_id'], 'limit' => 10, 'not_theirs' => 1, 'custom' => 0 ) );
		
		$content = $this->registry->getClass('output')->getTemplate('profile')->tabStatusUpdates( $statuses, $actions, $member );
		
		/* Replace Macros and return */
		$content = $this->registry->output->replaceMacros( $content );
		
		return $content ? $content : $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'err_no_status_to_show' );
	}
	
}