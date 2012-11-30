<?php

/**
 * Invision Power Services
 * IP.Board v3.0.4
 * API file for hooks to call to
 * Last Updated: $Date: 2009-07-08 21:23:44 -0400 (Wed, 08 Jul 2009) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 4856 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class hooksApi
{
	/**#@+
	 * Registry object
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
	 * @param	object		Registry object
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry )
	{
		/* Make object */
		$this->registry = $registry;
		$this->DB       = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang     = $this->registry->getClass('class_localization');
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache    = $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
	}
	
	/**
	 * Show the who's chatting block on the board index
	 *
	 * @access	public
	 * @return	string		HTML output
	 */
	public function whosChatting()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$member_ids         = array();
		$to_load            = array();
		
		//-----------------------------------------
		// Check module/app
		//-----------------------------------------

		if( !IPSLib::appIsInstalled('ipchat') OR !$this->settings['ipschat_online'] )
		{
			return '';
		}

		//-----------------------------------------
		// Check
		//-----------------------------------------
		
		if ( ! $this->settings['ipschat_whos_chatting'] )
		{
			return '';
		}
		
		//-----------------------------------------
		// Sort and show :D
		//-----------------------------------------
		
		if ( is_array( $this->caches['chatting'] ) AND count( $this->caches['chatting'] ) )
		{
			foreach( $this->caches['chatting'] as $id => $data )
			{
				if ( $data['updated'] < ( time() - 120 ) )
				{
					continue;
				}
				
				$to_load[ $id ] = $id;
			}
		}

		//-----------------------------------------
		// Got owt?
		//-----------------------------------------

		if ( count($to_load) )
		{
			$_seenIds	= array();
			
			$this->DB->build( array( 'select' => 'm.member_id, m.members_display_name, m.member_group_id, m.members_seo_name',
												     'from'   => array( 'members' => 'm' ),
												     'where'  => "m.member_id IN(" . implode( ",", $to_load ) . ")",
	 												 'add_join' => array( 0 => array( 'select' => 's.id, s.login_type, s.current_appcomponent',
																					  'from'   => array( 'sessions' => 's' ),
																					  'where'  => 's.member_id=m.member_id',
																					  'type'   => 'left' ) ),
							)		);
			$this->DB->execute();
			
			while ( $m = $this->DB->fetch() )
			{
				if( in_array( $m['member_id'], $_seenIds ) )
				{
					continue;
				}
				
				//-----------------------------------------
				// @link	http://community.invisionpower.com/tracker/issue-20547-logout-from-the-board-still-shown-the-user-active/
				// @see 	ipchatMemberSync
				//-----------------------------------------
				
				if( !$m['id'] )
				{
					continue;
				}
				
				$_seenIds[]	= $m['member_id'];

				$_key						= $m['members_display_name'];
				$m['members_display_name'] = IPSLib::makeNameFormatted( $m['members_display_name'], $m['member_group_id'] );
								
				//if( $m['login_type'] )
				//{
				//	if ( $this->memberData['g_access_cp'] and ($this->settings['disable_admin_anon'] != 1) )
				//	{
				//		$member_ids[ $_key ] = "<a href='" . $this->registry->getClass('output')->buildSEOUrl( "showuser={$m['member_id']}", 'public', $m['members_seo_name'], 'showuser' ) . "'>{$m['members_display_name']}</a>";
				//	}
				//}
				//else
				//{
					$member_ids[ $_key ] = "<a href='" . $this->registry->getClass('output')->buildSEOUrl( "showuser={$m['member_id']}", 'public', $m['members_seo_name'], 'showuser' ) . "'>{$m['members_display_name']}</a>";
				//}
			}
		}		
		
		ksort($member_ids);
		
		//-----------------------------------------
		// Got owt?
		//-----------------------------------------
		
		$html	= '';
		
		if ( count( $member_ids ) )
		{
			$html = $this->registry->getClass('output')->getTemplate('boards')->whoschatting_show( intval(count($member_ids)), $member_ids );
		}
		else
		{
			if ( ! $this->settings['ipschat_hide_chatting'] )
			{
				$html = $this->registry->getClass('output')->getTemplate('boards')->whoschatting_empty();
			}
		}
		
		return $html;
	}

	/**
	 * Show a count of the number of chatters on the 'Chat' tab
	 *
	 * @access	public
	 * @return	string		HTML output
	 */
	public function getChatTabCount()
	{
		if( !IPSLib::appIsInstalled('ipchat') OR !$this->settings['ipschat_online'] )
		{
			return '';
		}
		
		$cache	= $this->cache->getCache('chatting');
		$count	= 0;
		
		//-----------------------------------------
		// Sort and show :D
		//-----------------------------------------
		
		if ( is_array( $this->caches['chatting'] ) AND count( $this->caches['chatting'] ) )
		{
			foreach( $this->caches['chatting'] as $id => $data )
			{
				if ( $data['updated'] < ( time() - 120 ) )
				{
					continue;
				}
				
				$count++;
			}
		}
		
		return $this->registry->getClass('output')->getTemplate('ipchat')->tabCount( $count );
	}
}