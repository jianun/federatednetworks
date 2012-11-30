<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Mark user as spammer
 * Last Updated: $Date: 2010-04-15 15:46:26 -0400 (Thu, 15 Apr 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 6133 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_profile_status extends ipsCommand 
{
	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		//-----------------------------------------
		// Security check
		//-----------------------------------------
		
		if ( $this->request['k'] != $this->member->form_hash )
		{
			$this->registry->getClass('output')->showError( 'no_permission', 20314, null, null, 403 );
		}
    	
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$info = array();
 		$id   = intval( $this->memberData['member_id'] );
 				
		//-----------------------------------------
		// Get HTML and skin
		//-----------------------------------------

		$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );

		//-----------------------------------------
		// Can we access?
		//-----------------------------------------
		
		if ( !$this->memberData['g_mem_info'] )
 		{
			$this->registry->output->showError( 'status_off', 10268, null, null, 403 );
		}

		if( !$id )
		{
			$this->registry->output->showError( 'status_off', 10269, null, null, 404 );
		}

		$newStatus	= trim( IPSText::getTextClass('bbcode')->stripBadWords( $this->request['new_status'] ) );
		
		IPSMember::save( $id, array( 'extendedProfile' => array( 'pp_status' => $newStatus, 'pp_status_update' => time() ) ) );

		$this->registry->output->redirectScreen( $this->lang->words['status_was_changed'], $this->settings['base_url'] . 'showuser=' . $id, $this->memberData['members_seo_name'] );
	}
}