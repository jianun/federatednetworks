<?php

/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Chat user control panel page
 * Last Updated: $LastChangedDate: 2009-07-28 20:26:37 -0400 (Tue, 28 Jul 2009) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Chat
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 4948 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class usercpForms_ipchat extends public_core_usercp_manualResolver implements interface_usercp
{
	/**
	 * Tab name
	 * This can be left blank and the application title will
	 * be used
	 *
	 * @access	public
	 * @var		string
	 */
	public $tab_name = "Chat";
	
	/**
	 * Default area code
	 *
	 * @access	public
	 * @var		string
	 */
	public $defaultAreaCode = 'ignored';
	
	/**
	 * OK Message
	 * This is an optional message to return back to the framework
	 * to replace the standard 'Settings saved' message
	 *
	 * @access	public
	 * @var		string
	 */
	public $ok_message = '';
	
	/**
	 * Hide 'save' button and form elements
	 * Useful if you have custom output that doesn't
	 * require it
	 *
	 * @access	public
	 * @var		bool
	 */
	public $hide_form_and_save_button = false;
	
	/**
	 * If you wish to allow uploads, set a value for this
	 *
	 * @access	public
	 * @var		integer
	 */
	public $uploadFormMax = 0;	
	
	/**
	 * Initiate this module
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
	{
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( "public_chat" ), "ipchat" );
		$this->tab_name	= ipsRegistry::getClass('class_localization')->words['ucptab__chat'];
	}
	
	/**
	 * Return links for this tab
	 * You may return an empty array or FALSE to not have
	 * any links show in the tab.
	 *
	 * The links must have 'area=xxxxx'. The rest of the URL
	 * is added automatically.
	 * 'area' can only be a-z A-Z 0-9 - _
	 *
	 * @access	public
	 * @author	Matt Mecham
	 * @return	array 		Links
	 */
	public function getLinks()
	{
		$array = array();

		$array[] = array( 'url'    => 'area=ignored',
						  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['m_ignore_userschat'],
						  'active' => $this->request['tab'] == 'ipchat' && $this->request['area'] == 'ignored' ? 1 : 0,
						  'area'   => 'ignored'
						 );

		return $array;
	}

	/**
	 * Run custom event
	 *
	 * If you pass a 'do' in the URL / post form that is not either:
	 * save / save_form or show / show_form then this function is loaded
	 * instead. You can return a HTML chunk to be used in the UserCP (the
	 * tabs and footer are auto loaded) or redirect to a link.
	 *
	 * If you are returning HTML, you can use $this->hide_form_and_save_button = 1;
	 * to remove the form and save button that is automatically placed there.
	 *
	 * @access	public
	 * @author	bfarber
	 * @param	string		Current area
	 * @return	mixed		html or void
	 */
	public function runCustomEvent( $currentArea )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$removeID		= intval( $this->request['id'] );
		
		if( isset( $this->memberData['_cache']['ignore_chat'] ) )
		{
			$cache		= $this->memberData['_cache']['ignore_chat'];
		}
		else
		{
			$cache		= array();
		}
		
		unset($cache[ $removeID ]);

		/* Rebuild cache */
		IPSMember::packMemberCache( $this->memberData['member_id'], array( 'ignore_chat' => $cache ), $this->memberData['_cache'] );
		
		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&amp;module=usercp&amp;tab=ipchat&amp;area=ignored" );
	}

	/**
	 * UserCP Form Show
	 *
	 * @author	bfarber
	 * @param	string		Current area as defined by 'get_links'
	 * @param	array		Any errors
	 * @return	string		Processed HTML
	 */
	public function showForm( $current_area, $errors=array() )
	{
		//-----------------------------------------
		// Where to go, what to see?
		//-----------------------------------------

		switch( $current_area )
		{
			default:
			case 'ignored':
				return $this->formIgnoredUsers();
			break;
		}
	}

	/**
	 * Show the ignored users
	 *
	 * @access	public
	 * @author	bfarber
	 * @return	string		Processed HTML
	 */
	public function formIgnoredUsers()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$final_users  = array();
 		$temp_users   = array();
 		
 		//-----------------------------------------
 		// Get ignored users
 		//-----------------------------------------
 		
		if( isset( $this->memberData['_cache']['ignore_chat'] ) )
		{
			$cache		= $this->memberData['_cache']['ignore_chat'];
		}
		else
		{
			$cache		= array();
		}

 		//-----------------------------------------
 		// Get members and check to see if they've
 		// since been moved into a group that cannot
 		// be ignored
 		//-----------------------------------------
 		
 		foreach( $cache as $_id => $data )
 		{
 			if ( intval($_id) )
 			{
 				$temp_users[] = $_id;
 			}
 		}
 		
 		if ( count($temp_users) )
 		{
 			$members = IPSMember::load( $temp_users, 'all' );
		
 			foreach( $members as $m )
 			{
 				$m['g_title'] = IPSLib::makeNameFormatted( $this->caches['group_cache'][ $m['member_group_id'] ]['g_title'], $m['member_group_id'] );
 				
 				$final_users[ $m['member_id'] ] = IPSMember::buildDisplayData( $m );
 			}
 		}

 		$this->request['newbox_1'] = $this->request['newbox_1'] ? $this->request['newbox_1'] : '';
 		
 		return $this->registry->getClass('output')->getTemplate('ipchat')->ignoredUsersForm( $final_users );
	}

	/**
	 * UserCP Form Check
	 *
	 * @access	public
	 * @author	bfarber
	 * @param	string		Current area as defined by 'get_links'
	 * @return	string		Processed HTML
	 */
	public function saveForm( $current_area )
	{
		//-----------------------------------------
		// Where to go, what to see?
		//-----------------------------------------

		switch( $current_area )
		{
			default:
			case 'ignoredusers':
				return $this->saveIgnoredUsers();
			break;
		}
	}
	
	/**
	 * UserCP Save Form: Ignore Users
	 *
	 * @access	public
	 * @return	array	Errors
	 */
	public function saveIgnoredUsers()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$newName = $this->request['newbox_1'];
		
		if ( ! trim( $newName ) )
		{
			return array( 0 => $this->lang->words['ignoreuser_nomem'] );
		}
		
		//-----------------------------------------
		// Load
		//-----------------------------------------
		
		$member = IPSMember::load( $newName, 'core', 'displayname' );
		
		if ( ! $member['member_id'] )
		{
			return array( 0 => $this->lang->words['ignoreuser_nomem'] );
		}
		
		if ( $member['member_id'] == $this->memberData['member_id'] )
		{
			return array( 0 => $this->lang->words['ignoreuser_cannot'] );
		}
		
		//-----------------------------------------
		// Already ignoring?
		//-----------------------------------------
		
		if( isset( $this->memberData['_cache']['ignore_chat'] ) )
		{
			$cache		= $this->memberData['_cache']['ignore_chat'];
		}
		else
		{
			$cache		= array();
		}
		
		if ( array_key_exists( $member['member_id'], $cache ) )
		{
			return array( 0 => $this->lang->words['ignoreuser_already'] );
		}
		
		//-----------------------------------------
		// Can we ignore them?
		//-----------------------------------------
		
		if ( $member['_canBeIgnored'] !== TRUE )
		{
			return array( 0 => $this->lang->words['ignoreuser_cannot'] );
	 	}

		//-----------------------------------------
		// Add it
		//-----------------------------------------

		$cache[ $member['member_id'] ]	= $member['member_id'];
						
		/* Rebuild cache */
		IPSMember::packMemberCache( $this->memberData['member_id'], array( 'ignore_chat' => $cache ), $this->memberData['_cache'] );
		
		return TRUE;
	}
}