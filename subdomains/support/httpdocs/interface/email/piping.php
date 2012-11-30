#!/usr/bin/php -q
<?php
/**
 * Invision Power Services
 * Incoming Email Handler - Piping
 * Last Updated: $Date: 2010-06-25 07:37:32 -0400 (Fri, 25 Jun 2010) $
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2010 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		12th February 2010
 * @version		$Revision: 6575 $
 */
 
 	//--------------------------------------
 	// Init
 	//--------------------------------------
 	  	
	require_once( '../initdata.php' );
	define( 'DOC_IPS_ROOT_PATH', "../" );
	define( 'IPS_ROOT_PATH', "../" . CP_DIRECTORY . '/' );
	define( 'IPS_KERNEL_PATH', "../ips_kernel/" );
	
	//-----------------------------------------
	// Get the Email
	//-----------------------------------------
	
	$email = file_get_contents( $debug ? "../email.txt" : 'php://stdin' );
	
	require_once ( IPS_KERNEL_PATH . 'classIncomingEmail.php' );
	$incomingEmail = new classIncomingEmail( $email );
	$incomingEmail->route();
	
	exit();