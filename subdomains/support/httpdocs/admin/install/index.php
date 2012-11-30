<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Installation Gateway
 * Last Updated: $LastChangedDate: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		14th May 2003
 * @version		$Rev: 5713 $
 */

define( 'IPB_THIS_SCRIPT', 'admin' );
define( 'IPS_IS_UPGRADER', FALSE );
define( 'IPS_IS_INSTALLER', TRUE );

require_once( '../../initdata.php' );

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

/**
* Are we overwriting an existing IP.Board 2 installation?
*/
if ( file_exists( DOC_IPS_ROOT_PATH . 'sources/ipsclass.php' ) )
{
	@header( "Location: http://" . $_SERVER["SERVER_NAME"] . str_replace( "/install/", "/upgrade/", $_SERVER["PHP_SELF"] ) );
	exit();
}

require_once( IPS_ROOT_PATH . 'setup/sources/base/ipsRegistry_setup.php' );
require_once( IPS_ROOT_PATH . 'setup/sources/base/ipsController_setup.php' );

ipsController::run();

exit();

?>