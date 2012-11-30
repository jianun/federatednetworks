<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * XML gateway file
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5713 $
 *
 */

/**
* Turn off 'friendly' URLs
*/
define( 'ALLOW_FURLS', FALSE );

require_once( './initdata.php' );

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );

ipsController::run();

exit();