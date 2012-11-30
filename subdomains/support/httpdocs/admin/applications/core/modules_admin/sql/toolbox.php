<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * SQL Admin
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5713 $
 *
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_core_sql_toolbox extends ipsCommand
{
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Require the right driver file */
		require_once( IPS_ROOT_PATH . 'applications/core/modules_admin/sql/' . strtolower( ipsRegistry::dbFunctions()->getDriverType() ) . '.php' );
		$dbdriver = new admin_core_sql_toolbox_module();
		$dbdriver->makeRegistryShortcuts( $registry );
		$dbdriver->doExecute( $registry );
	}
}