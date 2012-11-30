<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Member property updater (AJAX)
 * Last Updated: $Date: 2010-03-22 22:39:27 -0400 (Mon, 22 Mar 2010) $
 * </pre>
 *
 * @author 		$Author: josh $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Revision: 5986 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_member_form__forums implements admin_member_form
{	
	/**
	* Tab name
	* This can be left blank and the application title will
	* be used
	*
	* @access	public
	* @var		string		Tab name
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
	* @param    array 			Member data
	* @return   array 			Array of links
	*/
	public function getSidebarLinks( $member=array() )
	{
	
		//-----------------------------------------
		// Load lang
		//-----------------------------------------
				
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_forums' ), 'forums' );
		
		
		$array = array();
				
		$array[] = array( 'img'   => '', 
						  'url'   => 'section=tools&amp;module=tools&amp;do=deleteposts',
						  'title' => ipsRegistry::getClass('class_localization')->words['m_deltitle'] );
						  
		$array[] = array( 'img'   => '', 
						  'url'   => 'section=tools&amp;module=tools&amp;do=deletesubscriptions',
						  'title' => ipsRegistry::getClass('class_localization')->words['m_delsubs'] );
	
		return $array;
	}
	
	/**
	* Returns content for the page.
	*
	* @access	public
	* @author	Matt Mecham
	* @param    array 				Member data
	* @return   array 				Array of tabs, content
	*/
	public function getDisplayContent( $member=array(), $tabsUsed=5 )
	{
		//-----------------------------------------
		// Load skin
		//-----------------------------------------
		
		$this->html = ipsRegistry::getClass('output')->loadTemplate('cp_skin_member_form', 'forums');

		//-----------------------------------------
		// Load lang
		//-----------------------------------------
				
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_forums' ), 'forums' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_member_form' ), 'forums' );
		
		//-----------------------------------------
		// Get member data
		//-----------------------------------------

	//	$member 			= IPSMember::load( $member['member_id'], 'extendedProfile' );
		$member['_avatar']	= str_replace( '<img ', '<img id="MF__avatar" ', IPSMember::buildAvatar( $member ) );

		//-----------------------------------------
		// Show...
		//-----------------------------------------
		
		return array( 'tabs' => $this->html->acp_member_form_tabs( $member, ( $tabsUsed + 1 ) ), 'content' => $this->html->acp_member_form_main( $member, ( $tabsUsed + 1 ) ), 'tabsUsed' => 1 );
	}
	
	/**
	* Process the entries for saving and return
	*
	* @access	public
	* @author	Brandon Farber
	* @return   array 				Multi-dimensional array (core, extendedProfile) for saving
	*/
	public function getForSave()
	{
		$return = array( 'core' => array(), 'extendedProfile' => array() );
		
		$return['core']['posts']				= intval(ipsRegistry::$request['posts']);
		$return['core']['view_avs']				= intval(ipsRegistry::$request['view_avs']);
		$return['core']['view_img']				= intval(ipsRegistry::$request['view_img']);
		$return['core']['restrict_post']		= ipsRegistry::$request['post_indef'] ? 1 : ( ipsRegistry::$request['post_timespan'] > 0 ? IPSMember::processBanEntry( array( 'timespan' => intval(ipsRegistry::$request['post_timespan']), 'unit' => ipsRegistry::$request['post_units']  ) ) : '' );
		$return['core']['mod_posts']			= ipsRegistry::$request['mod_indef'] ? 1 : ( ipsRegistry::$request['mod_timespan'] > 0 ? IPSMember::processBanEntry( array( 'timespan' => intval(ipsRegistry::$request['mod_timespan']), 'unit' => ipsRegistry::$request['mod_units']  ) ) : '' );
		$return['core']['org_perm_id']			= ipsRegistry::$request['org_perm_id'] ? ',' . implode( ",", ipsRegistry::$request['org_perm_id'] ) . ',' : '';
		
		return $return;
	}
	

}