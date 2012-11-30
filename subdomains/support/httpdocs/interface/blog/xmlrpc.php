<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Blog XMLRPC Interface
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Blog
 * @link		http://www.invisionpower.com
 * @version		$Revision: 5713 $
 *
 */

/**
* Script type
*
*/
define( 'IPB_THIS_SCRIPT', 'api' );
define( 'IPB_LOAD_SQL'   , 'queries' );

/**
* Matches IP address of requesting API
* Set to 0 to not match with IP address
*/
define( 'CVG_IP_MATCH', 1 );

require_once( '../../initdata.php' );

//===========================================================================
// MAIN PROGRAM
//===========================================================================

define( 'IPS_CLASS_PATH', IPS_ROOT_PATH . 'sources/classes/' );

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );

//-----------------------------------------
// Set up cookie stuff
//-----------------------------------------

$registry = ipsRegistry::instance();
$registry->init();

IPSCookie::$sensitive_cookies = array( 'session_id', 'ipb_admin_session_id', 'member_id', 'pass_hash' );

//--------------------------------
//  Set up our vars
//--------------------------------

$registry->DB()->obj['use_shutdown'] = 0;

//--------------------------------
// Set debug mode
//--------------------------------

$registry->DB()->setDebugMode( ipsRegistry::$settings['sql_debug'] == 1 ? intval($_GET['debug']) : 0 );

//===========================================================================
// Create the XML-RPC Server
//===========================================================================

require_once( IPS_KERNEL_PATH . 'classApiServer.php' );
$server		= new classApiServer();
$api		= $server->decodeRequest();

//===========================================================================
// Define Service
//===========================================================================

$valid_api = 0;

switch( $api )
{
	case 'blogger':
		$valid_api = 1;
	break;
	
	case 'wp':
	case 'metaWeblog':
		$api = 'metaWeblog';
		$valid_api = 1;
	break;
}

if( $valid_api )
{
	require_once( DOC_IPS_ROOT_PATH . 'interface/blog/apis/server_' . strtolower( $api ) . '.php' );

	$webservice = new xmlrpc_server( $registry );
	$webservice->classApiServer =& $server;

	$server->addObjectMap( $webservice, 'UTF-8' );

	//-----------------------------------------
	// Process....
	//-----------------------------------------
	$server->getXmlRpc();
}
else
{
	$server->apiSendError( 100, "Requested API not found" );
}

exit;