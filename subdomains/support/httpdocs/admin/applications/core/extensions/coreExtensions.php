<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Core Sessions
 * Last Updated: $Date: 2010-01-21 03:39:17 -0500 (Thu, 21 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 5732 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
* Main loader class
*/
class publicSessions__core
{
	/**
	 * Return session variables for this application
	 *
	 * current_appcomponent, current_module and current_section are automatically
	 * stored. This function allows you to add specific variables in.
	 *
	 * @access	public
	 * @author	Matt Mecham
	 * @return	array
	 */
	public function getSessionVariables()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$array = array( 'location_1_type'   => '',
						'location_1_id'     => 0,
						'location_2_type'   => '',
						'location_2_id'     => 0 );
		
		return $array;
	}
	
	/**
	 * Parse/format the online list data for the records
	 *
	 * @access	public
	 * @author	Brandon Farber
	 * @param	array 			Online list rows to check against
	 * @return	array 			Online list rows parsed
	 */
	public function parseOnlineEntries( $rows )
	{
		if( !is_array($rows) OR !count($rows) )
		{
			return $rows;
		}
		
		$final		= array();	

		foreach( $rows as $row )
		{
			if( $row['current_appcomponent'] == 'core' )
			{
				if( $row['current_module'] == 'global' )
				{
					if( $row['current_section'] == 'login' )
					{
						$row['where_line']		= ipsRegistry::getClass( 'class_localization' )->words['WHERE_login'];
					}
				}
				else if( $row['current_module'] == 'search' )
				{
					$row['where_line']		= ipsRegistry::getClass( 'class_localization' )->words['WHERE_search'];
				}
				else if( $row['current_module'] == 'reports' )
				{
					$rcCache = ipsRegistry::cache()->getCache('report_cache');
					
					if( is_array( $rcCache ) )
					{
						if( $rcCache['group_access'][ ipsRegistry::member()->getProperty('member_group_id') ] == true )
						{
							$row['where_line'] = ipsRegistry::getClass( 'class_localization' )->words['WHERE_reports'];
						}
					}
				}
			}
			
			$final[ $row['id'] ]	= $row;
		}
		
		return $final;
	}
}
