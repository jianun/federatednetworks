<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Global Search
 * Last Updated: $Date: 2010-02-19 01:29:54 +0000 (Fri, 19 Feb 2010) $
 * </pre>
 *
 * @author 		$Author: josh $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5855 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class search_engine
{
	/**
	 * Date range restriction start
	 *
	 * @access	protected
	 * @var		integer
	 */		
	protected $search_begin_timestamp = 0;
	
	/**
	 * Date range restriction end
	 *
	 * @access	protected
	 * @var		integer
	 */		
	protected $search_end_timestamp   = 0;

	/**
	 * Array of conditions for this search
	 *
	 * @access	protected
	 * @var		array
	 */		
	protected $whereConditions        = array();
	
	/**
	 * Sphinx client object
	 *
	 * @access	public
	 * @var		object
	 */
	public $sphinxClient;
	
	/**
	 * Setup registry objects
	 *
	 * @access	public
	 * @param	object	ipsRegistry $registry
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry )
	{
		/* Make object */
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
		
		/* Do we have the sphinxes? */
		if( ! file_exists( 'sphinxapi.php' ) )
		{
			$registry->output->showError( 'sphinx_api_missing', 10182 );
		}

		/* Load Sphinx */
		require( 'sphinxapi.php' );
		$this->sphinxClient = new SphinxClient();

		$this->sphinxClient->SetServer( $this->settings['search_sphinx_server'], intval( $this->settings['search_sphinx_port'] ) );
		$this->sphinxClient->SetMatchMode( SPH_MATCH_EXTENDED );
		$this->sphinxClient->SetLimits( 0, 1000 );
	}
	
	/**
	 * Perform a search.
	 * Returns an array of a total count (total number of matches)
	 * and an array of IDs matching the required number based on pagination. The ids returned would be based on the filters and type of search
	 *
	 * So if we had 1000 replies, and we are on page 2 of 25 per page, we'd return 25 items offset by 25
	 *
	 * @access public
	 * @return array
	 */
	public function search()
	{
		/* Per app class handles this */
		throw new Exception("NO_SEARCH_AVAILABLE");
	}
	
	/**
	 * Perform the viewNewContent search
	 * Generic version
	 * Populates $this->_count and $this->_results
	 *
	 * @access	public
	 * @return	nothin'
	 */
	public function viewNewContent()
	{
		$app        = IPSSearchRegistry::get('in.search_app');
		$lastActive = IPSLib::fetchHighestNumber( array( intval( $this->memberData['_cache']['gb_mark__' . $app] ), intval( $this->memberData['last_visit'] ) ) );
		
		IPSSearchRegistry::set('in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set('in.search_sort_order', 'desc' );
		
		$this->setDateRange( $lastActive, time() );
		
		/* Force title search only */
		IPSSearchRegistry::set('opt.searchTitleOnly', true);
		
		return $this->search();
	}
	
	/**
	 * Perform the search
	 * Populates $this->_count and $this->_results
	 *
	 * @access	public
	 * @return	nothin'
	 */
	public function viewUserContent( $member )
	{
		$app = IPSSearchRegistry::get('in.search_app');
		
		IPSSearchRegistry::set('in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set('in.search_sort_order', 'desc' );
		IPSSearchRegistry::set('opt.searchTitleOnly', IPSSearchRegistry::get('in.view_by_title') );
		IPSSearchRegistry::set('display.onlyTitles' , IPSSearchRegistry::get('in.view_by_title') );
		IPSSearchRegistry::set('opt.noPostPreview'  , IPSSearchRegistry::get('in.view_by_title') );
		
		/* Set the member_id */
		$this->setCondition( 'member_id', '=', $member['member_id'] );		
		
		/* Cut by date for efficiency? */
		if ( $this->settings['search_ucontent_days'] )
		{
			$begin = ( $member['last_post'] ? $member['last_post'] : time() ) - 86400 * intval( $this->settings['search_ucontent_days'] );
			
			$this->setDateRange( $begin, time() );
		}
		
		return $this->search();
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
		
		/* Set time stamp */
		$this->setDateRange( time() - $seconds, time() );
		
		return $this->search();
	}
	
	/**
	 * Can handle boolean searching
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isBoolean()
	{
		return TRUE;
	}
	
	/**
	 * Formats search term for SQL
	 *
	 * @access	private
	 * @param	string		Raw IPB santized form input
	 * @return	array		array( 'search_term' => Safe string to use in SQL, 'removed' => array of removed search terms )
	 */
	public function formatSearchTerm( $search_term )
	{
		$isBoolean      = $this->isBoolean();
		$andor          = isset( $this->request['andor_type'] ) ? $this->request['andor_type'] : $this->settings['s_andor_type'];
		$removedTerms	= array();
		
		/* Fix up some sanitized HTML */
		$search_term  = str_replace( "&amp;"    , '&'    ,  IPSText::parseCleanValue( rawurldecode( $search_term ) ) );
		$search_term  = str_replace( "&quot;"   , '"'    ,  $search_term );
		$search_term  = IPSText::mbstrtolower( $search_term );
		
		/* Check for disallowed search terms */
		while( preg_match_all( "/(?:^|\s+)(img|quote|code|html|javascript|a href|color|span|div|border|style)(?:\s+|$)/", $search_term, $removed_search_terms ) )
		{
			$removedTerms[]	= $removed_search_terms[0][0];
			$search_term	= preg_replace( "/(^|\s+)(?:img|quote|code|html|javascript|a href|color|span|div|border|style)(\s+|$)/", str_replace( "  ", " ", "$1$2" ), $search_term );
		}		
		
		/* Remove some formatting */
		//$search_term = str_replace( array( '|', '\\', '/' ), '', $search_term );
		// | is an OR operator for sphinx - don't want to block globally
		$search_term = str_replace( array( '\\', '/' ), '', $search_term );
		
		/* Sphinx chars are not allowed */
		$search_term = str_replace( array( '.', ')', '(', '!', '@', '[', ']' ), '', $search_term );
		
		if ( $andor == 'or' )
		{
			$search_term = preg_replace( "/\s+(?!-|~)/", " | ", $search_term );
		}
		
		return array( 'search_term' => $search_term, 'removed' => $removedTerms );
	}
	
	/**
	 * Restrict the date range that the search is performed on
	 *
	 * @access	public
	 * @param	int		$begin	Start timestamp
	 * @param	int		[$end]	End timestamp
	 * @return	void
	 */
	public function setDateRange( $begin, $end=0 )
	{
		$this->search_begin_timestamp = $begin;
		$this->search_end_timestamp   = $end;
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
		return $column;
	}
	
	/**
	 * mySQL function for adding special search conditions
	 *
	 * @access	public
	 * @param	string	$column		sql table column for this condition
	 * @param	string	$operator	Operation to perform for this condition, ex: =, <>, IN, NOT IN
	 * @param	mixed	$value		Value to check with
	 * @param	string	$comp		Comparison type
	 * @return	void
	 */
	public function setCondition( $column, $operator, $value, $comp='AND' )
	{
		/* This is restricted by the indexes searched */
		if( $column == 'app' )
		{
			return;
		}
		
		$column = $this->remapColumn( $column );
		
		if( !$column )
		{
			return;
		}

		/* Build the condition based on operator */
		switch( strtoupper( $operator ) )
		{
			case 'IN':
				$this->sphinxClient->setFilter( $column, explode( ',', $value ) );
			break;

			case 'NOT IN':
				$this->sphinxClient->setFilter( $column, explode( ',', $value ), TRUE );
			break;

			case '=':
				$this->sphinxClient->setFilter( $column, array( $value ) );
			break;

			case '!=':
			case '<>':
				$this->sphinxClient->setFilter( $column, array( $value ), TRUE );
			break;

			default:
				echo "<b>Unhandled sphinx search operator: {$operator}</b><br />";
			break;
		}
	}
	
	/**
	 * Allows you to specify multiple conditions that are chained together
	 *
	 * @access	public
	 * @param	array	$conditions	Array of conditions, each element has 3 keys: column, operator, value, see the setCondition function for information on each
	 * @param	string	$inner_comp	Comparison operator to use inside the chain
	 * @param	string	$outer_comp	Comparison operator to use outside the chain
	 * @return	void
	 */
	public function setMultiConditions( $conditions, $inner_comp='OR', $outer_comp='AND' )
	{	
		/* Loop through the conditions to build the statement */
		return;
	}
	
	/**
	 * Checks and logs any errors
	 *
	 * @access	public
	 * @return	void
	 */
	public function logSphinxWarnings()
	{	
		$error   = $this->sphinxClient->GetLastError();
		$warning = $this->sphinxClient->GetLastWarning();
		
		if ( $error )
		{
			IPSDebug::addLogMessage( "Sphinx Error: $error", 'sphinx_error_' . date('m_d_y'), $error, TRUE );
		}
		
		if ( $warning )
		{
			IPSDebug::addLogMessage( "Sphinx Warning: $warning", 'sphinx_warning_' . date('m_d_y'), $warning, TRUE );
		}
	}
}