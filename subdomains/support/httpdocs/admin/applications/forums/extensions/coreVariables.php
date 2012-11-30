<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Core variables extensions
 * Defines the reset array, which caches to load, how to recache those caches, and the bitwise array
 * Last Updated: $Date: 2010-03-22 22:39:27 -0400 (Mon, 22 Mar 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5986 $ 
 **/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$_RESET = array();

# ALL
if ( ( isset( $_REQUEST['CODE'] ) AND $_REQUEST['CODE'] ) or ( isset( $_REQUEST['code'] ) AND $_REQUEST['code'] ) )
{
	$_RESET['do'] = ( $_REQUEST['CODE'] ) ? $_REQUEST['CODE'] : $_REQUEST['code'];
}

//-----------------------------------------
// Extension File: Registered Caches
//-----------------------------------------

$_LOAD = array();

# BOARD INDEX
if ( ( ! $_GET['section'] AND ! $_GET['module'] ) OR ( $_GET['module'] == 'forums' AND $_GET['section'] == 'boards' ) )
{
	$_LOAD['chatting']			= 1;
	$_LOAD['birthdays']			= 1;
	$_LOAD['calendar']			= 1;
	$_LOAD['calendars']			= 1;
	$_LOAD['ranks']				= 1;
	$_LOAD['reputation_levels']	= 1;
}

# TOPIC
if ( isset( $_GET['showtopic'] ) OR $_GET['module'] == 'forums' AND $_GET['section'] == 'topics' )
{
	$_LOAD['badwords']			= 1;
	$_LOAD['emoticons']			= 1;
	$_LOAD['bbcode']			= 1;
	$_LOAD['multimod']			= 1;
	$_LOAD['ranks']				= 1;
	$_LOAD['profilefields']		= 1;
	$_LOAD['reputation_levels']	= 1;
	$_LOAD['sharelinks']		= 1;
}

# Forum
if ( isset( $_GET['showforum'] ) OR $_GET['module'] == 'forums' AND $_GET['section'] == 'forums' )
{
	$_LOAD['ranks']				= 1;
	$_LOAD['reputation_levels']	= 1;
	
	// Needed for forum rules...
	$_LOAD['badwords']			= 1;
	$_LOAD['emoticons']			= 1;
	$_LOAD['bbcode']			= 1;
}

# POST and RULES
if ( $_GET['module'] == 'post' OR $_GET['module'] == 'extras' )
{
	$_LOAD['badwords']			= 1;
	$_LOAD['bbcode']			= 1;
	$_LOAD['emoticons']			= 1;
	$_LOAD['ranks']				= 1;
	$_LOAD['reputation_levels']	= 1;
}

# ANNOUNCEMENT
if ( isset( $_GET['showannouncement'] ) OR $_GET['module'] == 'forums' AND $_GET['section'] == 'announcements' )
{
        $_LOAD['badwords']                      = 1;
        $_LOAD['bbcode']                        = 1;
        $_LOAD['emoticons']                     = 1;
        $_LOAD['ranks']                         = 1;
        $_LOAD['reputation_levels']     = 1;
}

$CACHE['attachtypes'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'forums' ) . '/modules_admin/attachments/types.php',
								'recache_class'    => 'admin_forums_attachments_types',
							    'recache_function' => 'attachmentTypeCacheRebuild' 
							);

$CACHE['multimod'] = array( 
							'array'            => 1,
							'allow_unload'     => 0,
							'default_load'     => 1,
							'recache_file'     => IPSLib::getAppDir( 'forums' ) . '/modules_admin/forums/multimods.php',
							'recache_class'    => 'admin_forums_forums_multimods',
							'recache_function' => 'multiModerationRebuildCache' 
						);
						
$CACHE['moderators'] = array( 
								'array'            => 1,
							    'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'forums' ) . '/modules_admin/forums/moderator.php',
								'recache_class'    => 'admin_forums_forums_moderator',
							    'recache_function' => 'rebuildModeratorCache' 
							);
						

$CACHE['announcements'] = array( 
								'array'            => 1,
							    'allow_unload'     => 0,
						        'default_load'     => 1,
						        'recache_file'     => IPSLib::getAppDir( 'forums' ) . '/modules_public/forums/announcements.php',
							    'recache_class'    => 'public_forums_forums_announcements',
						        'recache_function' => 'announceRecache' 
						    	);

						
//-----------------------------------------
// Bitwise Options
//-----------------------------------------

$_BITWISE = array( 'moderators' => array( 'bw_flag_spammers',
										  'bw_mod_soft_delete',
										  'bw_mod_un_soft_delete',
										  'bw_mod_soft_delete_see',
										  'bw_mod_soft_delete_topic',
										  'bw_mod_un_soft_delete_topic',
										  'bw_mod_soft_delete_topic_see',
										  'bw_mod_soft_delete_reason',
										  'bw_mod_soft_delete_see_post' ) );
