<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Profile AJAX Ignore User
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_ajax_ignore extends ipsAjaxCommand 
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
		/* Get ignore user quick call file */
		require_once( IPSLib::getAppDir( 'members' ) . '/modules_public/profile/ignore.php' );
		$library = new public_members_profile_ignore( $registry );
		$library->makeRegistryShortcuts( $registry );

		switch( $this->request['do'] )
		{
			default:
			case 'add':
				$result	= $library->ignoreMember( $this->request['memberID'], 'topics' );
			break;
			
			case 'remove':
				$result	= $library->stopIgnoringMember( $this->request['memberID'], 'topics' );
			break;
			
			case 'addPM':
				$result	= $library->ignoreMember( $this->request['memberID'], 'messages' );
			break;
			
			case 'removePM':
				$result	= $library->stopIgnoringMember( $this->request['memberID'], 'messages' );
			break;
		}
		
		$this->returnJsonArray( $result );
	}
}