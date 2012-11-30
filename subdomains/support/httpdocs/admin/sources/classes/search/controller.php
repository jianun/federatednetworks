<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Global Search
 * Last Updated: $Date: 2010-02-22 17:06:11 +0000 (Mon, 22 Feb 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5859 $
 */ 

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * Slightly nice way of dumping vars and stuff globally
 * Yeah, it's an evil global variable, but it's FANCY
 */
class IPSSearchRegistry {

	/**
	 * Registry vars
	 * @var	array
	 */
	static private $_vars = array();
	 
	/**
	 * Set var
	 * @access	public
	 * @param	mixed		Key
	 * @param	mixed		value
	 */
	static public function set( $k, $v )
	{
		if ( $k and isset( $v ) )
		{
			self::$_vars[ $k ] = $v;
		}
	}
	
	/**
	 * Get var
	 * @access	public
	 * @param	mixed
	 */
	static public function get( $k )
	{
		if ( isset( self::$_vars[ $k ] ) )
		{
			return self::$_vars[ $k ];
		}
		
		return null;
	}
}

/**
 * Ties in:
 * extensions/search/form.php
 * extensions/search/{engine}.php
 * extensions/search/format.php
 */
class IPSSearch
{
	/**#@+
	 * Registry Object Shortcuts
	 *
	 * @access	public
	 * @var		object
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	/**#@-*/
	
	/**
	 * Plug in for search
	 * @access	private
	 * @var		object
	 */
	private $SEARCH;
	
	/**
	 * Plug in for formatting results
	 * @access	private
	 * @var		object
	 */
	private $FORMAT;
	
	/**
	 * App
	 * @access	private
	 * @var		string
	 */
	private $_app;
	
	/**
	 * Engine
	 * @access	private
	 * @var		string
	 */
	private $_engine;
	
	/**
	 * Result count
	 *
	 * @access	private
	 * @param	int
	 */
	private $_count;
	
	/**
	 * Result array
	 *
	 * @access	private
	 * @param	array
	 */
	private $_results;
	
	static public $aso;
	static public $ask;
	static public $ast;
	
	/**
	 * Setup registry objects
	 *
	 * @access	public
	 * @param	object	ipsRegistry $registry
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry, $engine, $app )
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
		
		/* Set engine */
		$this->_engine    = strtolower( IPSText::alphanumericalClean( $engine ) );
		
		/* Set app */
		$this->_app       = strtolower( IPSText::alphanumericalClean( $app ) );
		
		/* Quick check */
		if ( ! file_exists( IPS_ROOT_PATH . 'sources/classes/search/engines/' . $this->_engine . '.php' ) )
		{
			/* Try SQL */
			if ( ! $this->_engine != 'sql' )
			{
				$this->_engine = 'sql';
				
				if ( ! file_exists( IPS_ROOT_PATH . 'sources/classes/search/engines/' . $this->_engine . '.php' ) )
				{
					throw new Exception( "NO_SUCH_ENGINE" );
				}
			}
			else
			{
				throw new Exception( "NO_SUCH_ENGINE" );
			}
		}
		
		if ( ! isset( ipsRegistry::$applications[ $this->_app ] ) )
		{
			throw new Exception( "NO_SUCH_APP" );
		}
		
		/* Set in registry */
		IPSSearchRegistry::set( 'global.engine', $this->_engine );
		IPSSearchRegistry::set( 'global.app'   , $this->_app );
		
		/* Load up the relevant engines */
		$_className = 'search_' . $this->_engine;
		
		require_once( IPS_ROOT_PATH . 'sources/classes/search/format.php' );
		
		/* Got an app specific file? Lets hope so */
		if ( file_exists( IPSLib::getAppDir( $this->_app ) . '/extensions/search/format.php' ) )
		{	
			/* We may not have sphinx specific stuff, so... */
			if ( ! file_exists( IPSLib::getAppDir( $this->_app ) . '/extensions/search/engines/' . $this->_engine . '.php' ) )
			{
				$this->_engine = 'sql';
				
				if ( ! file_exists( IPSLib::getAppDir( $this->_app ) . '/extensions/search/engines/' . $this->_engine . '.php' ) )
				{
					throw new Exception( "NO_SUCH_APP_ENGINE" );
				}
			}
			
			require_once( IPS_ROOT_PATH . 'sources/classes/search/engines/' . $this->_engine . '.php' );
			require_once( IPSLib::getAppDir( $this->_app ) . '/extensions/search/engines/' . $this->_engine . '.php' );
			
			/* SEARCH classname */
			$_className = 'search_engine_' . $this->_app;
			
			$this->SEARCH = new $_className( $registry );
			
			/* FORMAT file */
			require_once( IPSLib::getAppDir( $this->_app ) . '/extensions/search/format.php' );
			
			/* FORMAT classname */
			$_className = 'search_format_' . $this->_app;
			
			$this->FORMAT = new $_className( $registry );
			
			/* Grab config */
			require( IPSLib::getAppDir( $this->_app ) . '/extensions/search/config.php' );
			
			if ( is_array( $CONFIG ) )
			{
				foreach( $CONFIG as $k => $v )
				{
					IPSSearchRegistry::set( 'config.' . $k, $v );
				}
			}
		}
		else
		{
			throw new Exception( "NO_SUCH_APP_ENGINE" );
		}
		
		/* Multi content types */
		if ( IPSSearchRegistry::get( 'config.contentTypes' ) )
		{
			$c = IPSSearchRegistry::get( 'config.contentTypes' );
			
			if ( is_array( $c ) AND count( $c ) )
			{
				/* Set up default content type if supported */
				IPSSearchRegistry::set( $this->_app . '.searchInKey' , $c[0] );
				
				/* Filter specific search */
				if ( isset( $this->request['search_app_filters'][ $this->_app ]['searchInKey'] ) )
				{
					IPSSearchRegistry::set( $this->_app . '.searchInKey', $this->request['search_app_filters'][ $this->_app ]['searchInKey'] );
				}
			}
		}
	}
	
	/**
	 * Magic __call methods
	 * Aka too lazy to create a proper function
	 */
	public function __call( $funcName, $args )
	{
 		/* Output format stuff.. */
		switch ( $funcName )
		{
			case 'isBoolean':
				return $this->SEARCH->isBoolean();
			break;
			case 'formatSearchTerm':
				return $this->SEARCH->formatSearchTerm( $args[0] );
			break;
			case 'getResultCount':
				return $this->_count;
			break;
			case 'getResultSet':
				return $this->_results;
			break;
			case 'fetchTemplates':
				return $this->FORMAT->fetchTemplates();
			break;
			case 'fetchSortDropDown':
				return $this->SEARCH->fetchSortDropDown();
			break;
		}
 	}
 	
 	/**
	 * Generic: Return sort drop down
	 * 
	 *
	 * @access	public
	 * @param	string	App
	 * @return	array
	 */
	public function fetchSortDropDown( $app='' )
	{
		$app = ( $app ) ? $app : $this->_app;
		
		/* results page? */
		$filter = ( ! IPSSearchRegistry::get('view.search_form') AND IPSSearchRegistry::get( $app . '.searchInKey' )	) ? IPSSearchRegistry::get( $app . '.searchInKey' ) : '';
		
		if ( file_exists( IPSLib::getAppDir( $app ) . '/extensions/search/form.php' ) )
		{
			require_once( IPSLib::getAppDir( $app ) . '/extensions/search/form.php' );
			$_class = 'search_form_' . $app;
			
			if ( class_exists( $_class ) )
			{
				$_obj = new $_class();				
				$_dd  = $_obj->fetchSortDropDown();
				
				if ( $filter )
				{
					return $_dd[ $filter ];
				}
				else
				{
					return $_dd;
				}
			}
		}
		
		return array( 'date' => $this->lang->words['s_search_type_0'] );
	}
	
	/**
	 * Generic: Return sort in
	 * 
	 *
	 * @access	public
	 * @param	string	[App]
	 * @return	array
	 */
	public function fetchSortIn( $app='' )
	{
		$app = ( $app ) ? $app : $this->_app;
		
		if ( file_exists( IPSLib::getAppDir( $app ) . '/extensions/search/form.php' ) )
		{
			require_once( IPSLib::getAppDir( $app ) . '/extensions/search/form.php' );
			$_class = 'search_form_' . $app;
			
			if ( class_exists( $_class ) )
			{
				$_obj = new $_class();
				
				if ( method_exists( $_obj, 'fetchSortIn' ) )
				{			
					return $_obj->fetchSortIn();
				}
			}
		}
		
		return FALSE;
	}
 	
	/**
	 * Returns boxes for the search form
	 *
	 * @access	public
	 * @param	boolean		Grab all apps or just the current one
	 * @return	array
	 */	
	public function getHtml( $allApps = TRUE )
	{
		/* INIT */
		$filtersHtml = '';
		
		/* Loop through apps */		
		foreach( ipsRegistry::$applications as $app )
		{
			if( IPSLib::appisSearchable( $app['app_directory'] ) )
			{
				if ( ! $allApps and $app['app_directory'] != $this->_app )
				{
					continue;
				}
				
				/* got custom filter? */
				if ( file_exists( IPSLib::getAppDir( $app['app_directory'] ) . '/extensions/search/form.php' ) )
				{
					require_once( IPSLib::getAppDir( $app['app_directory'] ) . '/extensions/search/form.php' );
					$_class = 'search_form_' . $app['app_directory'];
				
					if ( class_exists( $_class ) and method_exists( $_class, 'getHtml' ) )
					{
						$_obj = new $_class();
						
						$filtersHtml[ $app['app_directory'] ] = $_obj->getHtml();
					}
					else
					{
						$filtersHtml[ $app['app_directory'] ] = array( 'title' => $app['app_public_title'], 'html' => '' );
					}
				}
				else
				{
					$filtersHtml[ $app['app_directory'] ] = array( 'title' => $app['app_public_title'], 'html' => '' );
				}
				
				$filtersHtml[ $app['app_directory'] ]['sortDropIn']   = $this->fetchSortIn( $app['app_directory'] );
				$filtersHtml[ $app['app_directory'] ]['sortDropDown'] = $this->fetchSortDropDown( $app['app_directory'] );
			}
		}
	
		return $filtersHtml;
	}
	
	/**
	 * Perform the search
	 * Populates $this->_count and $this->_results
	 *
	 * @access	public
	 * @return	nothin'
	 */		
	public function search()
	{
		$APP        = IPSSearchRegistry::get('in.search_app');
		$filterData = $this->request['search_app_filters'][ $APP ];
		
		/* Check for an author filter */
		if ( IPSSearchRegistry::get('in.search_author') )
		{
			/* Query the member id */
			$mem = $this->DB->buildAndFetch( array( 'select' => 'member_id', 
													'from'   => 'members', 
													'where'  => "members_display_name='" . $this->DB->addSlashes( IPSSearchRegistry::get('in.search_author') ) . "'"  )	 );
			
			IPSSearchRegistry::set('opt.searchAuthor', true );
			
			/* Add the condition to our search */
			$this->SEARCH->setCondition( 'member_id', '=', $mem['member_id'] ? $mem['member_id'] : -1 );
		}

		/* Check for application specific filters - just need to do active app here */
		$filterData = $this->SEARCH->buildFilterSQL( $filterData );

		if( $filterData )
		{
			if ( isset( $filterData[0] ) )
			{
				foreach( $filterData as $_data )
				{
					$this->SEARCH->setCondition( $_data['column'], $_data['operator'], $_data['value'], 'AND' );
				}
			}
			else
			{
				$this->SEARCH->setCondition( $filter_data['column'], $filter_data['operator'], $filter_data['value'], 'OR' );
			}
		}
		

		/* Check Date Range */
		if( IPSSearchRegistry::get('in.search_date_start') || IPSSearchRegistry::get('in.search_date_end') )
		{
			/* Start Range Date */
			$search_date_start = 0;

			if( IPSSearchRegistry::get('in.search_date_start') )
			{
				$search_date_start = strtotime( IPSSearchRegistry::get('in.search_date_start') );
				/* Correct for timezone...hopefully */
				$search_date_start += abs( $this->registry->class_localization->getTimeOffset() );
			}

			/* End Range Date */
			$search_date_end = 0;

			if( IPSSearchRegistry::get('in.search_date_end') AND IPSSearchRegistry::get('in.search_date_end') != 'now' )
			{
				$search_date_end = strtotime( IPSSearchRegistry::get('in.search_date_end') );
				/* Correct for timezone...hopefully */
				$search_date_end   += abs( $this->registry->class_localization->getTimeOffset() );
			}
						
			/* If the times are exactly equaly, we're going to assume they are trying to search all posts from one day */
			if( ( $search_date_start && $search_date_end ) && $search_date_start == $search_date_end )
			{
				$search_date_end += 86400;
			}

			$this->SEARCH->setDateRange( $search_date_start, $search_date_end );
		}
		
		/* Init session */
		$processId = $this->_startSession();
		
		/* Run the search */
		$results = $this->SEARCH->search();
		
		/* Set data */
		$this->_count   = intval( $results['count'] );
		$this->_results = $results['resultSet'];
		
		/* Now format results */
		if ( count( $this->_results ) )
		{
			$this->_results = $this->FORMAT->processResults( $this->_results );
			
			/* Now generate HTML */
			$this->_results = $this->FORMAT->parseAndFetchHtmlBlocks( $this->_results );
		}
		
		/* Kill session */
		$this->_endSession( $processId );
	}	
	
	/**
	 * Perform the search
	 * Populates $this->_count and $this->_results
	 *
	 * @access	public
	 * @return	nothin'
	 */
	public function viewNewContent()
	{
		IPSSearchRegistry::set('display.onlyTitles', true);
		
		/* Hard fix mobile app users to VNC based on unread content */
		if ( $this->member->isMobileApp )
		{
			$this->memberData['bw_vnc_type'] = 1;
		}
		
		/* Run the search */
		$results = $this->SEARCH->viewNewContent();
		
		/* Set data */
		$this->_count   = intval( $results['count'] );
		$this->_results = $results['resultSet'];
		
		/* Now format results */
		if ( count( $this->_results ) )
		{
			$this->_results = $this->FORMAT->processResults( $this->_results );
			
			/* Now generate HTML */
			$this->_results = $this->FORMAT->parseAndFetchHtmlBlocks( $this->_results );
		}
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
		/* Run the search */
		$results = $this->SEARCH->viewUserContent( $member );
		
		/* Set data */
		$this->_count   = intval( $results['count'] );
		$this->_results = $results['resultSet'];
		
		/* Now format results */
		if ( count( $this->_results ) )
		{
			$this->_results = $this->FORMAT->processResults( $this->_results );

			/* Now generate HTML */
			$this->_results = $this->FORMAT->parseAndFetchHtmlBlocks( $this->_results );
		}
	}
	
	
	/**
	 * Perform the search
	 *
	 * @access	public
	 * @return	nothin'
	 */
	public function viewActiveContent()
	{
		/* Bit 'o set up */
		IPSSearchRegistry::set('opt.searchTitleOnly', IPSSearchRegistry::get('in.view_by_title') );
		IPSSearchRegistry::set('display.onlyTitles', true);
		IPSSearchRegistry::set('opt.searchTitleOnly', true);
		
		/* Do we have a period? */
		switch( IPSSearchRegistry::get('in.period') )
		{
			case 'today':
			default:
				$date	= 86400;		// 24 hours
			break;
			
			case 'week':
				$date	= 604800;		// 1 week
			break;
			
			case 'weeks':
				$date	= 1209600;		// 2 weeks
			break;
			
			case 'month':
				$date	= 2592000;		// 30 days
			break;
			
			case 'months':
				$date	= 15552000;		// 6 months
			break;
			
			case 'year':
				$date	= 31536000;		// 365 days
			break;
		}
		
		/* Set date up */
		IPSSearchRegistry::set('in.period_in_seconds', $date );
		
		/* Run the search */
		$results = $this->SEARCH->viewActiveContent();
		
		/* Set data */
		$this->_count   = intval( $results['count'] );
		$this->_results = $results['resultSet'];
		
		/* Now format results */
		if ( count( $this->_results ) )
		{
			$this->_results = $this->FORMAT->processResults( $this->_results );
			
			/* Now generate HTML */
			$this->_results = $this->FORMAT->parseAndFetchHtmlBlocks( $this->_results );
		}
	}
	
	/**
	 * Flag a search session
	 *
	 * @access private
	 * @return int			Process ID
	 */
	private function _startSession()
	{
		/**
		 * If we've already run a search and it's not clear, kill it now
		 */
		if( $this->member->sessionClass()->session_data['search_thread_id'] )
		{
			$this->DB->return_die	= true;
			$this->DB->kill( $this->member->sessionClass()->session_data['search_thread_id'] );
			$this->DB->return_die	= false;
		}

		/**
		 * Store the process id
		 */
		$processId	= $this->DB->getThreadId();
		
		if ( $processId )
		{
			$this->DB->update( 'sessions', array( 'search_thread_id' => $processId, 'search_thread_time' => time() ), "id='" . $this->member->session_id . "'" );
		}
		
		return $processId;
	}
	
	/**
	 * End a search session
	 *
	 * @access private
	 * @return void
	 */
	private function _endSession( $processId )
	{
		if ( $processId )
		{
			$this->DB->update( 'sessions', array( 'search_thread_id' => 0, 'search_thread_time' => 0 ), "id='" . $this->member->session_id . "'" );
		}
	}
	
	/**
	 * Custom sort function to avoid filesorts in the system
	 *
	 * @access	public
	 * @param	array 		A
	 * @param	array		B
	 * @return	boolean
	 */
	static function usort( $a, $b )
	{
		switch ( self::$ast )
		{
			case 'numeric':
			case 'numerical':
				if ( self::$aso == 'asc' )
				{
					return ($a[ self::$ask ] > $b[ self::$ask ]) ? +1 : -1;
				}
				else
				{
					return ($a[ self::$ask ] < $b[ self::$ask ]) ? +1 : -1;
				}
			break;
			case 'string':
				if ( self::$aso == 'asc' )
				{
					return strcasecmp($a[ self::$ask ], $b[ self::$ask ]) <= 0 ? -1 : +1;
				}
				else
				{
					return strcasecmp($a[ self::$ask ], $b[ self::$ask ]) <= 0 ? +1 : -1;
				}
			break;
		}
	}
}
