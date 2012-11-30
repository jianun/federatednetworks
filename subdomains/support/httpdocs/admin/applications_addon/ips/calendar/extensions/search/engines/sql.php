<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Basic Calendar Search
 * Last Updated: $Date: 2010-02-23 12:38:11 +0000 (Tue, 23 Feb 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Calendar
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5861 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class search_engine_calendar extends search_engine
{
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry )
	{		
		parent::__construct( $registry );
	}
	
	/**
	 * Perform a search.
	 * Returns an array of a total count (total number of matches)
	 * and an array of IDs ( 0 => 1203, 1 => 928, 2 => 2938 ).. matching the required number based on pagination. The ids returned would be based on the filters and type of search
	 *
	 * So if we had 1000 replies, and we are on page 2 of 25 per page, we'd return 25 items offset by 25
	 *
	 * @access public
	 * @return array
	 */
	public function search()
	{
		/* INIT */ 
		$count       		= 0;
		$results     		= array();
		$sort_by     		= IPSSearchRegistry::get('in.search_sort_by');
		$sort_order         = IPSSearchRegistry::get('in.search_sort_order');
		$search_term        = IPSSearchRegistry::get('in.clean_search_term');
		$content_title_only = IPSSearchRegistry::get('opt.searchTitleOnly');
		$post_search_only   = IPSSearchRegistry::get('opt.onlySearchPosts');
		$order_dir 			= ( $sort_order == 'asc' ) ? 'asc' : 'desc';
		$sortKey			= '';
		$sortType			= '';
		$rows               = array();
		
		if ( IPSSearchRegistry::get('opt.noPostPreview') OR IPSSearchRegistry::get('display.onlyTitles') )
		{
			$group_by = 'c.event_id';
		}
		
		/* Sorting */
		switch( $sort_by )
		{
			default:
			case 'date':
				$sortKey  = 'c.event_unix_from';
				$sortType = 'numerical';
			break;
			case 'title':
				$sortKey  = 'c.event_title';
				$sortType = 'string';
			break;
		}
		
		/* Query the count */	
		$count = $this->DB->buildAndFetch(
											array( 
													'select'   => 'COUNT(*) as total_results',
													'from'	   => array( 'cal_events' => 'c' ),
 													'where'	   => $this->_buildWhereStatement( $search_term, $content_title_only ),
 													'group'    => $group_by,
													'add_join' => array(
																		array(
																				'from'   => array( 'permission_index' => 'i' ),
																				'where'  => "i.perm_type='calendar' AND i.perm_type_id=c.event_calendar_id",
																				'type'   => 'left',
																			),
																		array(
																				'from'   => array( 'profile_friends' => 'friend' ),
																				'where'  => 'friend.friends_member_id=c.event_member_id AND friend.friends_friend_id=' . $this->memberData['member_id'],
																				'type'   => 'left',
																			),
																		)													
										)  );
		
		/* Do the search */
		$this->DB->build( array( 
								'select'   => "c.*",
								'from'	   => array( 'cal_events' => 'c' ),
 								'where'	   => $this->_buildWhereStatement( $search_term, $content_title_only ),
								'group'    => $group_by,
								'order'    => $sortKey . ' ' . $sort_order,
								'limit'    => array( IPSSearchRegistry::get('in.start'), IPSSearchRegistry::get('opt.search_per_page') ),
								'add_join' => array(
													array(
															'select' => 'i.*',
															'from'   => array( 'permission_index' => 'i' ),
															'where'  => "i.perm_type='calendar' AND i.perm_type_id=c.event_calendar_id",
															'type'   => 'left',
														),
													array(
															'select' => 'mem.members_display_name, mem.member_group_id, mem.mgroup_others',
															'from'   => array( 'members' => 'mem' ),
															'where'  => "mem.member_id=c.event_member_id",
															'type'   => 'left',
														),
													array(
															'from'   => array( 'profile_friends' => 'friend' ),
															'where'  => 'friend.friends_member_id=c.event_member_id AND friend.friends_friend_id=' . $this->memberData['member_id'],
															'type'   => 'left',
														),
													)													
									)		);
		$this->DB->execute();
		
		/* Sort */
		while( $r = $this->DB->fetch() )
		{
			$rows[] = $r;
		}
	
		/* Return it */
		return array( 'count' => $count['total_results'], 'resultSet' => $rows );
	}
	
		/**
	 * Perform the viewNewContent search
	 * Populates $this->_count and $this->_results
	 *
	 * @access	public
	 * @return	nothin'
	 */
	public function viewNewContent()
	{	
		/* Loop through the forums and build a list of forums we're allowed access to */
		$start		= IPSSearchRegistry::get('in.start');
		$perPage	= IPSSearchRegistry::get('opt.search_per_page');
		
		IPSSearchRegistry::set('in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set('in.search_sort_order', 'desc' );
		IPSSearchRegistry::set('opt.searchTitleOnly' , true );
		IPSSearchRegistry::set('display.onlyTitles'  , true );
		IPSSearchRegistry::set('opt.noPostPreview'   , true );
		
		/* Time */
		$oldStamp = intval( $this->memberData['last_visit'] ) ? intval( $this->memberData['last_visit'] ) : time();
		
		/* Start Where */
		$where		= array();
		$where[]	= $this->_buildWhereStatement( '' );

		/* Based on oldest timestamp */
		$where[] = "c.event_unix_from > " . $oldStamp;

		$where = implode( " AND ", $where );

		/* Fetch the count */
		$count = $this->DB->buildAndFetch(
											array( 
													'select'	=> 'COUNT(*) as total_results',
													'from'		=> array( 'cal_events' => 'c' ),
													'where'		=> $where,
													'add_join'	=> array(
																		array(
																				'from'   => array( 'permission_index' => 'i' ),
																				'where'  => "i.perm_type='calendar' AND i.perm_type_id=c.event_calendar_id",
																				'type'   => 'left',
																			),
																		array(
																				'from'   => array( 'profile_friends' => 'friend' ),
																				'where'  => 'friend.friends_member_id=c.event_member_id AND friend.friends_friend_id=' . $this->memberData['member_id'],
																				'type'   => 'left',
																			),
																		)													
										)  );
		
		/* Fetch the data */
		$events = array();
		
		if( $count['total_results'] )
		{
			$this->DB->build( array( 
										'select'	=> "c.*",
										'from'		=> array( 'cal_events' => 'c' ),
										'where'		=> $where,
										'order'		=> 'c.event_unix_from DESC',
										'limit'		=> array( $start, $perPage ),
										'add_join'	=> array(
															array(
																	'select' => 'i.*',
																	'from'   => array( 'permission_index' => 'i' ),
																	'where'  => "i.perm_type='calendar' AND i.perm_type_id=c.event_calendar_id",
																	'type'   => 'left',
																),
															array(
																	'select' => 'mem.members_display_name, mem.member_group_id, mem.mgroup_others',
																	'from'   => array( 'members' => 'mem' ),
																	'where'  => "mem.member_id=c.event_member_id",
																	'type'   => 'left',
																),
															array(
																	'from'   => array( 'profile_friends' => 'friend' ),
																	'where'  => 'friend.friends_member_id=c.event_member_id AND friend.friends_friend_id=' . $this->memberData['member_id'],
																	'type'   => 'left',
																),
															)													
							)		);
			$this->DB->execute();
			
			while( $row = $this->DB->fetch( $o ) )
			{
				$events[] = $row['id'];
			}
		}

		/* Return it */
		return array( 'count' => $count['count'], 'resultSet' => $events );
	}
	
		/**
	 * Perform the search
	 * Populates $this->_count and $this->_results
	 *
	 * @access	public
	 * @return	nothin'
	 */
	public function viewActiveContent()
	{
		$seconds = IPSSearchRegistry::get('in.period_in_seconds');
		
		/* Loop through the forums and build a list of forums we're allowed access to */
		$where		= array();
		$start		= IPSSearchRegistry::get('in.start');
		$perPage    = IPSSearchRegistry::get('opt.search_per_page');
		$imgIds	    = array();

		/* Start Where */
		$where		= array();
		$where[]	= $this->_buildWhereStatement( '' );
	
		/* Generate last post times */
		$where[] = "c.event_unix_from > " . intval( time() - $seconds );
		
		$where = implode( " AND ", $where );


		/* Fetch the count */
		$count = $this->DB->buildAndFetch(
											array( 
													'select'	=> 'COUNT(*) as total_results',
													'from'		=> array( 'cal_events' => 'c' ),
													'where'		=> $where,
													'add_join'	=> array(
																		array(
																				'from'   => array( 'permission_index' => 'i' ),
																				'where'  => "i.perm_type='calendar' AND i.perm_type_id=c.event_calendar_id",
																				'type'   => 'left',
																			),
																		array(
																				'from'   => array( 'profile_friends' => 'friend' ),
																				'where'  => 'friend.friends_member_id=c.event_member_id AND friend.friends_friend_id=' . $this->memberData['member_id'],
																				'type'   => 'left',
																			),
																		)
										)  );
		
		/* Fetch count */
		$events = array();
		
		if( $count['total_results'] )
		{
			$this->DB->build( array( 
										'select'	=> "c.*",
										'from'		=> array( 'cal_events' => 'c' ),
										'where'		=> $where,
										'order'		=> 'c.event_unix_from DESC',
										'limit'		=> array( $start, $perPage ),
										'add_join'	=> array(
															array(
																	'select' => 'i.*',
																	'from'   => array( 'permission_index' => 'i' ),
																	'where'  => "i.perm_type='calendar' AND i.perm_type_id=c.event_calendar_id",
																	'type'   => 'left',
																),
															array(
																	'select' => 'mem.members_display_name, mem.member_group_id, mem.mgroup_others',
																	'from'   => array( 'members' => 'mem' ),
																	'where'  => "mem.member_id=c.event_member_id",
																	'type'   => 'left',
																),
															array(
																	'from'   => array( 'profile_friends' => 'friend' ),
																	'where'  => 'friend.friends_member_id=c.event_member_id AND friend.friends_friend_id=' . $this->memberData['member_id'],
																	'type'   => 'left',
																),
															)
							)		);
			$this->DB->execute();

			while( $row = $this->DB->fetch( $o ) )
			{
				$events[] = $row;
			}
		}
		
		/* Return it */
		return array( 'count' => $count['total_results'], 'resultSet' => $events );
	}
	
	/**
	 * Perform the viewUserContent search
	 * Populates $this->_count and $this->_results
	 *
	 * @access	public
	 * @return	nothin'
	 */
	public function viewUserContent( $member )
	{
		/* Init */
		$start		= IPSSearchRegistry::get('in.start');
		$perPage	= IPSSearchRegistry::get('opt.search_per_page');
		IPSSearchRegistry::set( 'in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set( 'in.search_sort_order', 'desc' );
		IPSSearchRegistry::set( 'gallery.searchInKey', 'images' );
		
		/* Ensure we limit by date */
		$this->settings['search_ucontent_days'] = ( $this->settings['search_ucontent_days'] ) ? $this->settings['search_ucontent_days'] : 365;
		
		/* Start Where */
		$where	= array();
		$where[]	= $this->_buildWhereStatement( '' );
		
		/* Search by author */
		$where[] = "c.event_member_id=" . intval( $member['member_id'] );
	
		if ( $this->settings['search_ucontent_days'] )
		{
			$where[] = "c.event_unix_from > " . ( time() - ( 86400 * intval( $this->settings['search_ucontent_days'] ) ) );
		}
		
		$where = implode( " AND ", $where );
		
		/* Fetch the count */
		$count = $this->DB->buildAndFetch(
											array( 
													'select'	=> 'COUNT(*) as count',
													'from'		=> array( 'cal_events' => 'c' ),
													'where'		=> $where,
													'add_join'	=> array(
																		array(
																				'from'   => array( 'permission_index' => 'i' ),
																				'where'  => "i.perm_type='calendar' AND i.perm_type_id=c.event_calendar_id",
																				'type'   => 'left',
																			),
																		array(
																				'from'   => array( 'profile_friends' => 'friend' ),
																				'where'  => 'friend.friends_member_id=c.event_member_id AND friend.friends_friend_id=' . $this->memberData['member_id'],
																				'type'   => 'left',
																			),
																		)
										)  );

		/* Fetch the data */
		$events = array();
		
		if ( $count['count'] )
		{
			$this->DB->build( array( 
										'select'	=> "c.*",
										'from'		=> array( 'cal_events' => 'c' ),
										'where'		=> $where,
										'order'		=> 'c.event_unix_from DESC',
										'limit'		=> array( $start, $perPage ),
										'add_join'	=> array(
															array(
																	'select' => 'i.*',
																	'from'   => array( 'permission_index' => 'i' ),
																	'where'  => "i.perm_type='calendar' AND i.perm_type_id=c.event_calendar_id",
																	'type'   => 'left',
																),
															array(
																	'select' => 'mem.members_display_name, mem.member_group_id, mem.mgroup_others',
																	'from'   => array( 'members' => 'mem' ),
																	'where'  => "mem.member_id=c.event_member_id",
																	'type'   => 'left',
																),
															array(
																	'from'   => array( 'profile_friends' => 'friend' ),
																	'where'  => 'friend.friends_member_id=c.event_member_id AND friend.friends_friend_id=' . $this->memberData['member_id'],
																	'type'   => 'left',
																),
															)
							)		);
			$this->DB->execute();

			while( $row = $this->DB->fetch() )
			{
				$events[] = $row;
			}
		}

		/* Return it */
		return array( 'count' => $count['count'], 'resultSet' => $events );
	}
		
	/**
	 * Builds the where portion of a search string
	 *
	 * @access	private
	 * @param	string	$search_term		The string to use in the search
	 * @param	bool	$content_title_only	Search only title records
	 * @return	string
	 **/
	private function _buildWhereStatement( $search_term, $content_title_only=false )
	{		
		/* INI */
		$where_clause = array();
				
		if( $search_term )
		{
			if( $content_title_only )
			{
				$where_clause[] = "c.event_title LIKE '%{$search_term}%'";
			}
			else
			{
				$where_clause[] = "(c.event_title LIKE '%{$search_term}%' OR c.event_content LIKE '%{$search_term}%')";
			}
		}
		
		/* Exclude some items */
		if( !$this->memberData['g_is_supmod'] )
		{
			/* Approved */
			$where_clause[] = 'c.event_approved=1';
			
			/* Owner only */
			$where_clause[] = '(i.owner_only=0 OR c.event_member_id=' . $this->memberData['member_id'] . ')';
			
			/* Friend only */
			$where_clause[] = '(i.friend_only=0 OR friend.friends_id ' . $this->DB->buildIsNull( false ) . ')';
			
			/* Authorized users only */
			$where_clause[] = '(i.authorized_users ' . $this->DB->buildIsNull() . " OR i.authorized_users='' OR c.event_member_id=" . $this->memberData['member_id'] . " OR i.authorized_users LIKE '%," . $this->memberData['member_id'] . ",%')";
		}
		
		/* Date Restrict */
		if( $this->search_begin_timestamp && $this->search_end_timestamp )
		{
			$where_clause[] = $this->DB->buildBetween( "c.event_unix_from", $this->search_begin_timestamp, $this->search_end_timestamp );
		}
		else
		{
			if( $this->search_begin_timestamp )
			{
				$where_clause[] = "c.event_unix_from > {$this->search_begin_timestamp}";
			}
			
			if( $this->search_end_timestamp )
			{
				$where_clause[] = "c.event_unix_from < {$this->search_end_timestamp}";
			}
		}
		
		/* Add in AND where conditions */
		if( isset( $this->whereConditions['AND'] ) && count( $this->whereConditions['AND'] ) )
		{
			$where_clause = array_merge( $where_clause, $this->whereConditions['AND'] );
		}
		
		/* ADD in OR where conditions */
		if( isset( $this->whereConditions['OR'] ) && count( $this->whereConditions['OR'] ) )
		{
			$where_clause[] = '( ' . implode( ' OR ', $this->whereConditions['OR'] ) . ' )';
		}

		/* Permissions */
		$where_clause[] = $this->DB->buildRegexp( "i.perm_view", $this->member->perm_id_array );
		
		/* Event permissions */
		$where_clause[] = "((c.event_private=1 AND c.event_member_id=" . $this->memberData['member_id'] . ") OR (c.event_private=0 AND " . $this->DB->buildRegexp( "c.event_perms", $this->member->perm_id_array ) . "))";
			
		/* Build and return the string */
		return implode( " AND ", $where_clause );
	}
	
	/**
	 * Remap standard columns (Apps can override )
	 *
	 * @access	public
	 * @param	string	$column		sql table column for this condition
	 * @return	string				column
	 * @return	void
	 */
	public function remapColumn( $column )
	{
		$column = $column == 'member_id' ? 'c.event_member_id' : $column;

		return $column;
	}
		
	/**
	 * Returns an array used in the searchplugin's setCondition method
	 *
	 * @access	public
	 * @param	array 	$data	Array of forums to view
	 * @return	array 	Array with column, operator, and value keys, for use in the setCondition call
	 **/
	public function buildFilterSQL( $data )
	{
		/* INIT */
		$return = array();
		
		/* Set up some defaults */
		IPSSearchRegistry::set( 'opt.noPostPreview'  , false );
		IPSSearchRegistry::set( 'opt.onlySearchPosts', false );
		
		return array();
	}

	/**
	 * Can handle boolean searching
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isBoolean()
	{
		return false;
	}
}