<?php

/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Chat services ban user
 * Last Updated: $Date: 2009-02-04 15:03:59 -0500 (Wed, 04 Feb 2009) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Chat
 * @link		http://www.invisionpower.com
 * @since		Fir 12th Aug 2005
 * @version		$Revision: 3887 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class public_ipchat_ajax_ban extends ipsAjaxCommand
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
		// Check that we have the key
		//-----------------------------------------
		
		if( $this->settings['ipb_reg_number'] )
		{
			$this->settings['ipschat_account_key']	= $this->settings['ipb_reg_number'];
		}

		if ( ! $this->settings['ipschat_account_key'] )
		{
			$this->returnString( "no" );
		}
		
		//-----------------------------------------
		// Can we access?
		//-----------------------------------------

		$access_groups = explode( ",", $this->settings['ipschat_group_access'] );
		
		$my_groups = array( $this->memberData['member_group_id'] );
		
		if( $this->memberData['mgroup_others'] )
		{
			$my_groups = array_merge( $my_groups, explode( ",", IPSText::cleanPermString( $this->memberData['mgroup_others'] ) ) );
		}
		
		$access_allowed = false;
		
		foreach( $my_groups as $group_id )
		{
			if( in_array( $group_id, $access_groups ) )
			{
				$access_allowed = 1;
				break;
			}
		}
		
		if( !$access_allowed )
		{
			$this->returnString( "no" );
		}
		
		if( $this->memberData['chat_banned'] )
		{
			$this->returnString( "no" );
		}
		
		$permissions	= 0;
		
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
    	
    	if( !$permissions )
    	{
    		$this->returnString( "no" );
    	}
		
		//-----------------------------------------
		// Ban member
		//-----------------------------------------
		
		IPSMember::save( $this->request['id'], array( 'core' => array( 'chat_banned' => 1 ) ) );

		//-----------------------------------------
		// Something to return
		//-----------------------------------------
		
		$this->returnString( "ok" );
	}
}