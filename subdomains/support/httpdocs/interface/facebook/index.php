<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Main public executable wrapper.
 * Set-up and load module to run
 * Last Updated: $Date: 2010-06-25 06:36:57 -0400 (Fri, 25 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2008 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6574 $
 *
 */

define( 'IPS_ENFORCE_ACCESS', TRUE );
define( 'IPB_THIS_SCRIPT', 'public' );
require_once( '../../initdata.php' );

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );

$registry = ipsRegistry::instance();
$registry->init();

require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
$facebook = new facebook_connect( $registry );

/* IPB fiddles with CODE to make it DO */
if ( ! $_REQUEST['code'] AND $_REQUEST['do'] )
{
	$_REQUEST['code'] = $_REQUEST['do'];
}

/* User pinging the un-install app? */
if ( $_POST['fb_sig'] AND $_POST['fb_sig_uninstall'] )
{
	$facebook->userHasRemovedApp();
	exit();
}

if ( $_REQUEST['code'] )
{
	/* From the log in page */
	if ( $_REQUEST['key'] )
	{
		try
		{
			if ( ! intval( $_REQUEST['m'] ) )
			{
				$facebook->finishLogin();
			}
			else
			{
				$facebook->finishConnection();
			}
		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();
		
			switch( $msg )
			{
				default:
					$registry->getClass('output')->showError( 'fb_ohnoes', 999990, null, null, 403 );
				break;
				case 'FACEBOOK_NOT_SET_UP':
					$registry->getClass('output')->showError( 'fb_not_on', 999991, null, null, 403 );
				case 'NOT_REMOTE_MEMBER':
					$registry->getClass('output')->showError( 'fb_not_remote', 999992, null, null, 403 );
				break;
			}
		}
	}
	else
	{
		$facebook->finishConnection();
	}
}
else
{
	$facebook->redirectToConnectPage();
}

exit();

?>