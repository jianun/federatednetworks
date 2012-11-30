<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Login handler abstraction
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 5713 $
 *
 */

interface interface_login
{
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @param	array 		Configuration info for this method
	 * @param	array 		Custom configuration info for this method
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry, $method, $conf=array() );

	/**
	 * Authenticate via local database
	 *
	 * @param	string		Username [Username or Email Address must be supplied]
	 * @param	string		Email Address [Username or Email Address must be supplied]
	 * @param	string		Password
	 * @return	boolean		Authentication successful
	 */
	public function authLocal( $username, $email_address, $password );
	
	/**
	 * Create a record of the user locally
	 *
	 * @param	array 		Member information
	 * @return	void
	 */
	public function createLocalMember( $member );
	
	/**
	 * Normal authentication routine for the login method
	 *
	 * @param	string		Username  [Username or Email Address must be supplied]
	 * @param	string		Email Address  [Username or Email Address must be supplied]
	 * @param	string		Password
	 * @return	boolean
	 */
	public function authenticate( $username, $email_address, $password );
}
