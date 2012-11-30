<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Portal plugin: calendar
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Calendar
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ppi_calendar extends public_portal_portal_portal 
{
	/**
	 * Initialize module
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
 	{
 	}
 	
	/**
	 * Show the current calendar month on the portal
	 *
	 * @access	public
	 * @return	string		HTML content to replace tag with
	 */
	public function calendar_show_current_month()
	{
		/* If the calendar is not installed, just return */
		if( ! IPSLib::appIsInstalled( 'calendar' ) )
		{
			return '';
		}

		//-----------------------------------------
		// Grab calendar class
		//-----------------------------------------
		
		require_once( IPSLib::getAppDir( 'calendar', 'calendar' ) . '/calendars.php' );
		$calendar = new public_calendar_calendar_calendars();
		$calendar->makeRegistryShortcuts( $this->registry );
		$calendar->initCalendar(true);

		if( ! is_array( $calendar->calendar ) OR ! count( $calendar->calendar ) OR ! $calendar->can_read )
		{
			return'';
		}

 		//-----------------------------------------
 		// What now?
 		//-----------------------------------------
 		
 		$a = explode( ',', gmdate( 'Y,n,j,G,i,s', time() + ipsRegistry::getClass( 'class_localization')->getTimeOffset() ) );
		
		$now_date = array(
						  'year'    => $a[0],
						  'mon'     => $a[1],
						  'mday'    => $a[2],
						  'hours'   => $a[3],
						  'minutes' => $a[4],
						  'seconds' => $a[5]
						);
							   
 		$content = $calendar->getMiniCalendar( $now_date['mon'], $now_date['year'] );
 		
 		return $this->registry->getClass('output')->getTemplate('portal')->calendarWrap( $content );
  	}
}