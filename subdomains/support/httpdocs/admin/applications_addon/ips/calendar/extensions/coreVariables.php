<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Core variables for calendar
 * Last Updated: $Date: 2010-04-15 15:46:26 -0400 (Thu, 15 Apr 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Calendar
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6133 $ 
 **/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$_LOAD = array();


$_LOAD['calendars']  = 1;

$CACHE['calendars'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'calendar' ) . '/modules_admin/calendar/calendars.php',
								'recache_class'    => 'admin_calendar_calendar_calendars',
							    'recache_function' => 'calendarsRebuildCache' 
							);

$CACHE['birthdays'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'calendar' ) . '/modules_admin/calendar/calendars.php',
								'recache_class'    => 'admin_calendar_calendar_calendars',
							    'recache_function' => 'calendarRebuildCache' 
							);

$CACHE['calendar'] = array( 
								'array'            => 1,
								'allow_unload'     => 0,
							    'default_load'     => 1,
							    'recache_file'     => IPSLib::getAppDir( 'calendar' ) . '/modules_admin/calendar/calendars.php',
								'recache_class'    => 'admin_calendar_calendar_calendars',
							    'recache_function' => 'calendarRebuildCache' 
							);

$CACHE['rss_calendar'] = array(
						'array'				=> 1,
						'allow_unload'		=> 0,
						'default_load'		=> 1,
						'skip_rebuild_when_upgrading' => 1,
						'recache_file'		=> IPSLib::getAppDir( 'calendar' ) . '/modules_admin/calendar/calendars.php',
						'recache_class'		=> 'admin_calendar_calendar_calendars',
						'recache_function'	=> 'calendarRSSCache' 
						);