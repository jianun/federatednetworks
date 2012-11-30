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

class search_engine_core extends search_engine
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
		$got				= 0;
		
		if ( IPSSearchRegistry::get('opt.noPostPreview') OR IPSSearchRegistry::get('display.onlyTitles') )
		{
			$group_by = 'h.id';
		}
		
		/* Sorting */
		switch( $sort_by )
		{
			default:
			case 'date':
				$sortKey  = ( $content_title_only ) ? 'id' : 'id';
				$sortType = 'numerical';
			break;
			case 'title':
				$sortKey  = 'title';
				$sortType = 'string';
			break;
		}
		
		/* Query the count */	
		$count = $this->DB->buildAndFetch( array('select'   => 'COUNT(*) as total_results',
												 'from'	    => array( 'faq' => 'h' ),
		 										 'where'	=> $this->_buildWhereStatement( $search_term, $content_title_only ),
		 										 'group'    => $group_by,
												 'add_join' => array( array( 'from'   => array( 'permission_index' => 'i' ),
																			 'where'  => "i.perm_type='help' AND i.perm_type_id=1",
																			 'type'   => 'left' ) ) ) );
		
		
		
		/* Fetch data */
		$this->DB->build( array( 'select'   => 'h.*',
							     'from'	    => array( 'faq' => 'h' ),
								 'where'	=> $this->_buildWhereStatement( $search_term, $content_title_only ),
								 'group'    => $group_by,
								 'order'    => $sortKey . ' ' . $sort_order,
								 'limit'    => array( IPSSearchRegistry::get('in.start'), IPSSearchRegistry::get('opt.search_per_page') ),
								 'add_join' => array( array( 'from'   => array( 'permission_index' => 'i' ),
															 'where'  => "i.perm_type='help' AND i.perm_type_id=1",
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
	private function _buildWhereStatement( $search_term, $content_title_only=false )
	{		
		/* INI */
		$where_clause = array();
				
		if( $search_term )
		{
			if( $content_title_only )
			{
				$where_clause[] = "h.title LIKE '%{$search_term}%'";
			}
			else
			{
				$where_clause[] = "(h.title LIKE '%{$search_term}%' OR h.text LIKE '%{$search_term}%' OR h.description LIKE '%{$search_term}%')";
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
		if ( $column == 'member_id' )
		{
			return '';
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