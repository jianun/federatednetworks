<?php

/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Get user data for add user row
 * Last Updated: $Date: 2009-02-04 15:03:59 -0500 (Wed, 04 Feb 2009) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Chat
 * @link		http://www.invisionpower.com
 * @since		Oct 15, 2009
 * @version		$Revision: 3887 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class public_ipchat_ajax_adduser extends ipsAjaxCommand
{
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		//-----------------------------------------
		// Got sess ID and mem ID?
		//-----------------------------------------
		
		if ( ! $this->member->getProperty('member_id') )
		{
			$this->returnString( "no" );
		}
		
		//-----------------------------------------
		// Get member data and format
		//-----------------------------------------
		
		$user	= intval($this->request['user']);
		$id		= intval($this->request['id']);
		
		if( !$id OR !$user )
		{
			$this->returnString( "no" );
		}
		
		$member	= IPSMember::buildDisplayData( IPSMember::load( $id ) );
		
		//-----------------------------------------
		// Mod permissions
		//-----------------------------------------
		
		$my_groups = array( $this->memberData['member_group_id'] );
		
		if( $this->memberData['mgroup_others'] )
		{
			$my_groups = array_merge( $my_groups, explode( ",", IPSText::cleanPermString( $this->memberData['mgroup_others'] ) ) );
		}
		
		$permissions	= 0;
		$private		= 0;
		
		if( $this->settings['ipschat_mods'] )
		{
			$mod_groups = explode( ",", $this->settings['ipschat_mods'] );

			foreach( $my_groups as $group_id )
			{
				if( in_array( $group_id, $mod_groups ) )
				{
					$permissions = 1;
					break;
				}
			}
    	}
    	
		if( $this->settings['ipschat_private'] )
		{
			$mod_groups = explode( ",", $this->settings['ipschat_private'] );

			foreach( $my_groups as $group_id )
			{
				if( in_array( $group_id, $mod_groups ) )
				{
					$private = 1;
					break;
				}
			}
    	}

		//-----------------------------------------
		// Return output
		//-----------------------------------------
		
		$this->returnJsonArray( array(
								'html' 			=> ipsRegistry::getClass('output')->replaceMacros( $this->registry->getClass('output')->getTemplate('ipchat')->ajaxNewUser( array( 'user_id' => $user, 'moderator' => $permissions, 'private' => $private, 'member' => $member ) ) ),
								'prefix'		=> $this->settings['ipschat_format_names'] ? str_replace( '"', '__DBQ__', $member['prefix'] ) : '',
								'suffix'		=> $this->settings['ipschat_format_names'] ? str_replace( '"', '__DBQ__', $member['suffix'] ) : '',
								'name'			=> $member['members_display_name'],
								'_canBeIgnored'	=> $member['_canBeIgnored'],
								)	);
	}
}