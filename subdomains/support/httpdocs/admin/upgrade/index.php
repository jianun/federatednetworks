<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Upgrade gateway
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
define( 'IPS_IS_UPGRADER', TRUE );
define( 'IPS_IS_INSTALLER', FALSE );

require_once( '../../initdata.php' );

require_once( IPS_ROOT_PATH . 'setup/sources/base/ipsRegistry_setup.php' );
require_once( IPS_ROOT_PATH . 'setup/sources/base/ipsController_setup.php' );

ipsController::run();

exit();
