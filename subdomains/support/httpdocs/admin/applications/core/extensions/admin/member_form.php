<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Member property updater (AJAX)
 * Last Updated: $Date: 2010-01-15 14:28:09 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: josh $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 5715 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_member_form__core implements admin_member_form
{	
	/**
	 * Tab name
	 * This can be left blank and the application title will
	 * be used
	 *
	 * @access	public
	 * @var		string
	 */
	public $tab_name = "";

	
	/**
	 * Returns sidebar links for this tab
	 * You may return an empty array or FALSE to not have
	 * any links show in the sidebar for this block.
	 *
	 * The links must have 'section=xxxxx&module=xxxxx[&do=xxxxxx]'. The rest of the URL
	 * is added automatically.
	 *
	 * The image must be a full URL or blank to use a default image.
	 *
	 * Use the format:
	 * $array[] = array( 'img' => '', 'url' => '', 'title' => '' );
	 *
	 * @access	public
	 * @author	Matt Mecham
	 * @param	array 			Member data
	 * @return	array 			Array of links
	 */
	public function getSidebarLinks( $member=array() )
	{
		$array = array();

		if( $member['failed_login_count'] )
		{
			$array[] = array( 'img'   => '', 
							  'url'   => "app=members&amp;module=members&amp;section=tools&amp;do=do_locked&amp;auth_key=".ipsRegistry::member()->form_hash."&amp;mid_{$member['member_id']}=1&amp;type=unlock",
							  'title' => $this->lang->words['reset_failed_logins'] );
		}
	
		return $array;
	}
	
	/**
	 * Returns content for the page.
	 *
	 * @access	public
	 * @author	Matt Mecham
	 * @param	array 				Member data
	 * @return	array 				Array of tabs, content
	 */
	public function getDisplayContent( $member=array(), $tabsUsed=5 )
	{
		return array( 'tabs' => '', 'content' => '', 'tabsUsed' => 0 );
	}
	
	/**
	 * Process the entries for saving and return
	 *
	 * @access	public
	 * @author	Brandon Farber
	 * @return	array 				Multi-dimensional array (core, extendedProfile) for saving
	 */
	public function getForSave()
	{
		return array( 'core' => array(), 'extendedProfile' => array() );
	}
	

}