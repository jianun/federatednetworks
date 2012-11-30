<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Login handler abstraction : AJAX login
 * Last Updated: $Date: 2010-01-25 11:58:04 -0500 (Mon, 25 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 5747 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_ajax_facebook extends ipsAjaxCommand 
{
	/**
	 * Login handler object
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $han_login;
	
	/**
	 * Flag : Logged in
	 *
	 * @access	protected
	 * @var		boolean
	 */
	protected $logged_in		= false;
	
	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
    	/* What to do */
		switch( $this->request['do'] )
		{
			case 'getUserByFbId':
				$return = $this->_getUserByFbId();
			break;
		}
		
		/* Output */
		$this->returnHtml( $return );
	}
	
		
	/**
	 * Main AJAX log in routine
	 *
	 * @access	private
	 * @return	void		[Outputs JSON to browser AJAX call]
	 */
	private function _getUserByFbId()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$fbUid = is_numeric( $this->request['fbid'] ) ? $this->request['fbid'] : 0;
		$member = array( 'member_id' => 0 );
		
		if ( $fbUid )
		{
			$_mid = $this->DB->buildAndFetch( array( 'select' => 'member_id',
													 'from'   => 'members',
													 'where'  => 'fb_uid=' . $fbUid ) );
													
			if ( $_mid['member_id'] )
			{
				$member = IPSMember::load( $_mid['member_id'], 'all' );
			}
		}
		
		$this->returnJsonArray( $member );
	}
}