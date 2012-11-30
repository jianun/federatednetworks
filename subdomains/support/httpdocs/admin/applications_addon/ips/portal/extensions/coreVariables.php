<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Core variables for portal
 * Last Updated: $LastChangedDate: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Portal
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
* Array for holding reset information
*
* Populate the $_RESET array and ipsRegistry will do the rest
*/

$_RESET = array();

###### Redirect requests... ######

if( ! $_REQUEST['module'] AND $_REQUEST['app'] == 'portal' )
{
	$_RESET['module'] = 'portal';
}


$_LOAD = array();


$_LOAD['portal']		= 1;
$_LOAD['emoticons']		= 1;
$_LOAD['bbcode']		= 1;
$_LOAD['badwords']		= 1;
$_LOAD['calendars']		= 1;

$CACHE['portal'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'portal' ) . '/modules_admin/portal/portal.php',
								'recache_class'    => 'admin_portal_portal_portal',
							    'recache_function' => 'portalRebuildCache' 
							);
