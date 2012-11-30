<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Profile View
 * Last Updated: $Date: 2010-06-10 06:36:42 -0400 (Thu, 10 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: danc $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 6502 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_profile_photo extends ipsCommand
{
	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$info = array();
 		$id   = intval( $this->request['id'] );
 				
		//-----------------------------------------
		// Get HTML and skin
		//-----------------------------------------

		$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );

		//-----------------------------------------
		// Can we access?
		//-----------------------------------------
		
		if ( !$this->memberData['g_mem_info'] )
 		{
 			$this->registry->output->showError( 'photos_profiles_off', 10242, null, null, 403 );
		}
		
		if( !$id )
		{
			$this->registry->output->showError( 'photos_no_id', 10243, null, null, 404 );
		}

    	$member	= IPSMember::load( $id );
    	$member = IPSMember::buildDisplayData( $member );

    	$html	= $this->registry->getClass('output')->getTemplate('profile')->showPhoto( $member );

		//-----------------------------------------
		// Push to print handler
		//-----------------------------------------
		
		$this->registry->getClass('output')->setTitle( $this->lang->words['photo_title']  . ' - ' . ipsRegistry::$settings['board_name']);
		$this->registry->getClass('output')->popUpWindow( $html );
 	}
 	
}