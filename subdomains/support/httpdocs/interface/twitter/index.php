<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Main public executable wrapper.
 * Set-up and load module to run
 * Last Updated: $Date: 2010-06-16 04:32:48 -0400 (Wed, 16 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2008 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6540 $
 *
 */

define( 'IPS_ENFORCE_ACCESS', TRUE );
define( 'IPB_THIS_SCRIPT', 'public' );
require_once( '../../initdata.php' );

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );

$registry = ipsRegistry::instance();
$registry->init();

require_once( IPS_ROOT_PATH . 'sources/classes/twitter/connect.php' );
$twitter = new twitter_connect( $registry );

if ( $_REQUEST['oauth_token'] )
{
	/* From the log in page */
	if ( $_REQUEST['key'] )
	{
		try
		{
			if ( ! intval( $_REQUEST['m'] ) )
			{
				$twitter->finishLogin();
			}
			else
			{
				$twitter->finishConnection();
			}
		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();
		
			switch( $msg )
			{
				default:
					$registry->getClass('output')->showError( 'twit_ohnoes', 99999, null, null, 403 );
				break;
				case 'TWITTER_NOT_SET_UP':
					$registry->getClass('output')->showError( 'twit_not_on', 99999, null, null, 403 );
				case 'NOT_REMOTE_MEMBER':
					$registry->getClass('output')->showError( 'twit_not_remote', 99999, null, null, 403 );
				break;
			}
		}
	}
	else
	{
		$twitter->finishConnection();
	}
}
else
{
	$twitter->redirectToConnectPage();
}

exit();