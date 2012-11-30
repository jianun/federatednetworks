<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Portal plugin: recent topics
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$PORTAL_CONFIG = array();

/**
* Main plug in title
*
*/
$PORTAL_CONFIG['pc_title'] = 'IP.Board Recent Topics';

/**
* Plug in mini description
*
*/
$PORTAL_CONFIG['pc_desc']  = "Shows IP.Board recent topics with topic's first post";

/**
* Keyword for settings. This is the keyword
* entered into ibf_conf_settings_titles -> conf_title_keyword
* Can be left blank.
* PLEASE stick to the naming convention when entering a setting
* keyword: portal_{file_name_minus_php} This will prevent
* other keyword clashes. Likewise, when creating settings, choose
* NOT to cache them (they will be loaded at run time) and always
* prefix with {file_name_minus_php}_setting_key - for example
* If you had a setting called "export_forums" then please name it
* "recent_topics_export_forums". This will be available in
* $this->settings['recent_topics_export_forums'] in the
* main module.
*/
$PORTAL_CONFIG['pc_settings_keyword'] = "portal_recent_topics";

/**
* Exportable tags key must be in the naming format of:
* {file_name_minus_php}-tag. The value *MUST* be the function
* which it corresponds to. For example:
* 'recent_topics_last_x' => 'recent_topics_last_x'
* The portal will look for function 'recent_topics_last_x' in
* module "sources/portal_plugins/recent_topics.php" when it parses
* the tag <!--::recent_topics_last_x::-->
*
* @param array[ TAG ] = array( FUNCTION NAME, DESCRIPTION );
*/
$PORTAL_CONFIG['pc_exportable_tags']['recent_topics_last_x']             = array( 'recent_topics_last_x'            , 'Shows the last X topics with full post from the selected forums' );
$PORTAL_CONFIG['pc_exportable_tags']['recent_topics_discussions_last_x'] = array( 'recent_topics_discussions_last_x', 'Shows the last X topic titles from ALL viewable forums'          );
