<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Config for topics plugin
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
* Plug in name (Default tab name)
*/
$CONFIG['plugin_name']        = 'Topics';

/**
* Language string for the tab
*/
$CONFIG['plugin_lang_bit']    = 'pp_tab_topics';

/**
* Plug in key (must be the same as the main {file}.php name
*/
$CONFIG['plugin_key']         = 'topics';

/**
* Show tab?
*/
$CONFIG['plugin_enabled']     = 1;

/**
* Order
*/
$CONFIG['plugin_order'] = 3;