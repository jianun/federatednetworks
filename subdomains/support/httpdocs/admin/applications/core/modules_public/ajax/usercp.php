<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Login handler abstraction : AJAX UserCP functions
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
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

class public_core_ajax_usercp extends ipsAjaxCommand 
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
    	switch( $this->request['do'] )
    	{
			case 'displayNameCheck':
    			$this->displayNameCheck();
    			break;
    	}
	}
	
	/**
	 * Checks a display name
	 *
	 * @access	private
	 * @return	void		[Outputs JSON to browser AJAX call]
	 */
	private function displayNameCheck()
	{
		//-----------------------------------------
    	// INIT
    	//-----------------------------------------
    	
    	$name   = strtolower( $this->convertAndMakeSafe( $this->request['name'], 0 ) );
    	$name   = str_replace("&#43;", "+", $name );
    	$member = array();
		$return = TRUE;
    	$id     = intval( $this->request['member_id'] );
    	
    	# Set member ID
    	$id   = $this->memberData['member_id'] ? $this->memberData['member_id'] : $id;
    	
		//-----------------------------------------
		// Load member if required
		//-----------------------------------------
		
		if ( $id != $this->memberData['member_id'] )
		{
			$member = IPSMember::load( $id, 'all' );
		}
		else
		{
			$member = $this->member->fetchMemberData();
		}
		
		//-----------------------------------------
		// Test name
		//-----------------------------------------
		
		try
		{
			$return = IPSMember::getFunction()->checkNameExists( $name, $member );
		}
		catch( Exception $error )
		{
			$_msg = $error->getMessage();
			
			if ( $_msg == 'NO_MORE_CHANGES' )
			{
				$this->returnString( 'nomorechanges' );
				return;
			}
			
			# Really, we're not very interested why it didn't work at this point, so
			# just return with a 'found' string which will make a nice red cross and
			# force the user to choose another.
			
			$this->returnString('found');
			return;
		}
		
		//-----------------------------------------
		// So, what's it to be?
		//-----------------------------------------
		
		$this->returnString( ( $return === TRUE ) ? 'found' : 'notfound' );
	}
}