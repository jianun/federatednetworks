<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Basic Forum Search
 * Last Updated: $Date: 2010-02-23 12:38:11 +0000 (Tue, 23 Feb 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5861 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class search_engine_members extends search_engine
{
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry )
	{
		parent::__construct( $registry );
	}
	
	/**
	 * Decide what type of search we're using
	 *
	 * @access	public
	 * @return	array
	 */
	public function search()
	{
		/* Ok, now because the top bar search box defaults to members default search, which is profile comments, we need
		   to convince it to default to members if the user used the top search bar with members selected */
		if ( $this->request['fromMainBar'] )
		{
			IPSSearchRegistry::set('members.searchInKey', 'members');
		}
		
		if ( IPSSearchRegistry::get('members.searchInKey') AND IPSSearchRegistry::get('members.searchInKey') != 'comments' )
		{
			return $this->_membersSearch();
		}
		else
		{
			return $this->_commentsSearch();
		}
	}
	
	/**
	 * Perform a comment search.
	 * Returns an array of a total count (total number of matches)
	 * and an array of IDs ( 0 => 1203, 1 => 928, 2 => 2938 ).. matching the required number based on pagination. The ids returned would be based on the filters and type of search
	 *
	 * So if we had 1000 replies, and we are on page 2 of 25 per page, we'd return 25 items offset by 25
	 *
	 * @access public
	 * @return array
	 */
	public function _commentsSearch()
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
		$got				= 0;
		
		/* Sorting */
		switch( $sort_by )
		{
			default:
			case 'date':
				$sortKey  = 'comment_date';
				$sortType = 'numerical';
			break;
		}
		
		/* Not allowed to see profile information */
		if ( ! $this->memberData['g_mem_info'] )
		{
			return array( 'count' => 0, 'resultSet' => array() );
		}

		/* Query the count */	
		$count = $this->DB->buildAndFetch( array('select'   => 'COUNT(*) as total_results',
												 'from'	    => array( 'profile_comments' => 'c' ),
				 								 'where'	=> $this->_buildCommentsWhereStatement( $search_term, $content_title_only ),
												 'add_join' => array( array( 'from'   => array( 'profile_portal' => 'p' ),
																			 'where'  => "p.pp_member_id=c.comment_by_member_id",
																			 'type'   => 'left' ) ) ) );
		
		
		
		/* Fetch data */
		$this->DB->build( array('select'   => 'c.*',
								'from'	   => array( 'profile_comments' => 'c' ),
								'where'	   => $this->_buildCommentsWhereStatement( $search_term, $content_title_only ),
								'order'    => $sortKey . ' ' . $sort_order,
								'limit'    => array( IPSSearchRegistry::get('in.start'), IPSSearchRegistry::get('opt.search_per_page') ),
								'add_join' => array( array( 'select' => 'x.members_display_name as members_display_name_from, x.members_seo_name as members_seo_name_from',
															'from'   => array( 'members' => 'x' ),
															'where'  => "x.member_id=c.comment_by_member_id",
															'type'   => 'left' ),
													 array( 'select' => 'm.*',
															'from'   => array( 'members' => 'm' ),
															'where'  => "m.member_id=c.comment_for_member_id",
															'type'   => 'left' ),
													 array( 'select' => 'p.*',
															'from'   => array( 'profile_portal' => 'p' ),
															'where'  => "p.pp_member_id=c.comment_for_member_id",
															'type'   => 'left' ) ) ) );
		
		
		
		
		
		$this->DB->execute();

		/* Sort */
		while ( $_row = $this->DB->fetch() )
		{
			$rows[ $got ] = $_row;
							
			$got++;
		}
	
		/* Return it */
		return array( 'count' => $count['total_results'], 'resultSet' => $rows );
	}
	
	/**
	 * Perform a MEMBER search.
	 * Returns an array of a total count (total number of matches)
	 * and an array of IDs ( 0 => 1203, 1 => 928, 2 => 2938 ).. matching the required number based on pagination. The ids returned would be based on the filters and type of search
	 *
	 * So if we had 1000 replies, and we are on page 2 of 25 per page, we'd return 25 items offset by 25
	 *
	 * @access public
	 * @return array
	 */
	public function _membersSearch()
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
		$got				= 0;
		
		if ( IPSSearchRegistry::get('opt.noPostPreview') OR IPSSearchRegistry::get('display.onlyTitles') )
		{
			$group_by = 'm.member_id';
		}
		
		/* Sorting */
		switch( $sort_by )
		{
			default:
			case 'date':
				$sortKey  = ( $content_title_only ) ? 'member_id' : 'member_id';
				$sortType = 'numerical';
			break;
			case 'title':
				$sortKey  = 'members_l_display_name';
				$sortType = 'string';
			break;
		}
		
		/* Query the count */	
		$count = $this->DB->buildAndFetch( array('select'   => 'COUNT(*) as total_results',
												 'from'	    => array( 'members' => 'm' ),
				 								 'where'	=> $this->_buildWhereStatement( $search_term, $content_title_only ),
				 								 'group'    => $group_by,
												 'add_join' => array( array( 'from'   => array( 'profile_portal' => 'p' ),
																			'where'  => "p.pp_member_id=m.member_id",
																			'type'   => 'left' ) ) ) );
		
		
		
		/* Fetch data */
		$this->DB->build( array('select'   => 'm.*',
								'from'	   => array( 'members' => 'm' ),
								'where'	   => $this->_buildWhereStatement( $search_term, $content_title_only ),
								'group'    => $group_by,
								'order'    => $sortKey . ' ' . $sort_order,
								'limit'    => array( IPSSearchRegistry::get('in.start'), IPSSearchRegistry::get('opt.search_per_page') ),
								'add_join' => array( array( 'select' => 'p.*',
															'from'   => array( 'profile_portal' => 'p' ),
															'where'  => "p.pp_member_id=m.member_id",
															'type'   => 'left' ) ) ) );
		
		
		
		
		
		$this->DB->execute();

		/* Sort */
		while ( $_row = $this->DB->fetch() )
		{
			$rows[ $got ] = $_row;
							
			$got++;
		}
	
		/* Return it */
		return array( 'count' => $count['total_results'], 'resultSet' => $rows );
	}
	
	/**
	 * Builds the where portion of a search string
	 *
	 * @access	private
	 * @param	string	$search_term		The string to use in the search
	 * @param	bool	$content_title_only	Search only title records
	 * @return	string
	 **/
	private function _buildCommentsWhereStatement( $search_term, $content_title_only=false )
	{		
		/* INI */
		$where_clause = array();
				
		if( $search_term )
		{
			$where_clause[] = "c.comment_content LIKE '%" . strtolower( $search_term ) . "%'";
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
		
		/* Date Restrict */
		if( $this->search_begin_timestamp && $this->search_end_timestamp )
		{
			$where_clause[] = $this->DB->buildBetween( "c.comment_date", $this->search_begin_timestamp, $this->search_end_timestamp );
		}
		else
		{
			if( $this->search_begin_timestamp )
			{
				$where_clause[] = "c.comment_date > {$this->search_begin_timestamp}";
			}
			
			if( $this->search_end_timestamp )
			{
				$where_clause[] = "c.comment_date < {$this->search_end_timestamp}";
			}
		}
		
		$where_clause[] = "c.comment_approved=1";
			
		/* Build and return the string */
		return implode( " AND ", $where_clause );
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
				$where_clause[] = "m.members_l_display_name LIKE '%" . strtolower( $search_term ) . "%'";
			}
			else
			{
				$where_clause[] = "m.members_l_display_name LIKE '%" . strtolower( $search_term ) . "%' OR p.pp_bio_content LIKE '%{$search_term}%' OR p.signature LIKE '%{$search_term}%' OR p.pp_about_me LIKE '%{$search_term}%'";
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
		
		/* Date Restrict */
		if( $this->search_begin_timestamp && $this->search_end_timestamp )
		{
			$where_clause[] = $this->DB->buildBetween( "m.joined", $this->search_begin_timestamp, $this->search_end_timestamp );
		}
		else
		{
			if( $this->search_begin_timestamp )
			{
				$where_clause[] = "m.joined > {$this->search_begin_timestamp}";
			}
			
			if( $this->search_end_timestamp )
			{
				$where_clause[] = "m.joined < {$this->search_end_timestamp}";
			}
		}
			
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
		if ( IPSSearchRegistry::get( 'members.searchInKey') == 'comments' )
		{
			$column = ( $column == 'member_id' ) ? 'c.comment_by_member_id' : '';
		}
		
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
		//IPSSearchRegistry::set( 'members.searchInKey' , 'comments' );
		
		/* member specific search */
		/* Make default search type topics */
		if( isset( $data ) && is_array( $data ) && count( $data ) )
		{
			foreach( $data as $field => $_data )
			{ 
				/* CONTENT ONLY */
				if ( $field == 'searchInKey' AND $_data['searchInKey'] == 'members' )
				{
					//IPSSearchRegistry::set( 'member.searchType', 'members' );
				}
			}
		}

		
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
	
	/**
	 * Perform the viewNewContent search
	 * Forum Version
	 * Populates $this->_count and $this->_results
	 *
	 * @access	public
	 * @return	nothin'
	 */
	public function viewNewContent()
	{
		/* Set up some vars */
		IPSSearchRegistry::set('set.resultCutToDate', intval( $this->memberData['last_visit'] ) );
		
		return parent::viewNewContent();
	}
}