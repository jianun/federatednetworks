<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Global Search
 * Last Updated: $Date: 2010-06-30 21:01:53 -0400 (Wed, 30 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6593 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_search_search extends ipsCommand
{
	/**
	 * Generated output
	 *
	 * @access	private
	 * @var		string
	 */		
	private $output			= '';
	
	/**
	 * Page Title
	 *
	 * @access	private
	 * @var		string
	 */		
	private $title			= '';
	
	/**
	 * Object to handle searches
	 *
	 * @access	private
	 * @var		string
	 */	
	private $search_plugin	= '';
	
	/**
	 * Topics array
	 *
	 * @access	private
	 * @var		array
	 */
	private	$_topicArray	= array();
	private $_removedTerms  = array();
	
	/**
	 * Search controller
	 *
	 * @access	private
	 * @var		obj
	 */		
	private $searchController;
	private $_session;

	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Load language */
		$this->registry->class_localization->loadLanguageFile( array( 'public_search' ), 'core' );
		$this->registry->class_localization->loadLanguageFile( array( 'public_forums', 'public_topic' ), 'forums' );
		
		/* Reset engine type */
		$this->settings['search_method'] = ( $this->settings['search_method'] == 'traditional' ) ? 'sql' : $this->settings['search_method'];
		
		/* Special consideration for contextual search */
		if ( isset( $this->request['search_app'] ) AND strstr( $this->request['search_app'], ':' ) )
		{
			list( $app, $type, $id ) = explode( ':', $this->request['search_app'] );
			
			$this->request['search_app'] = $app;
			$this->request['cType']      = $type;
			$this->request['cId']		 = $id;
		}
		else
		{
			/* Force forums as default search */
			$this->request['search_in']      = ( $this->request['search_in'] AND IPSLib::appIsSearchable( $this->request['search_in'], 'search' ) ) ? $this->request['search_in'] : 'forums';
			$this->request['search_app']     = $this->request['search_app'] ? $this->request['search_app'] : $this->request['search_in'];
		}
		
		/* Check Access */
		$this->_canSearch();		
		
		/* Start session - needs to be called before the controller is initiated */
		$this->_startSession();
		
		/* Load the controller */
		require_once( IPS_ROOT_PATH. 'sources/classes/search/controller.php' );
		
		try
		{
			$this->searchController = new IPSSearch( $registry, $this->settings['search_method'], $this->request['search_app'] );
		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();
			
			/* Start session */
			$this->_endSession();
		
			switch( $msg )
			{
				case 'NO_SUCH_ENGINE':
				case 'NO_SUCH_APP':
				case 'NO_SUCH_APP_ENGINE':
					$this->registry->output->showError( sprintf( $this->lang->words['no_search_app'], ipsRegistry::$applications[ $this->request['search_app'] ]['app_title'] ), 10145.1 );
				break;
			}
		}
		
		/* Set up some defaults */
		IPSSearchRegistry::set('in.start', intval( $this->request['st'] ) );
		IPSSearchRegistry::set('opt.search_per_page', intval( $this->request['search_per_page'] ) ? intval( $this->request['search_per_page'] ) : 25 );
		
		/* Contextuals */
		if ( isset( $this->request['cType'] ) )
		{
			IPSSearchRegistry::set('contextual.type', $this->request['cType'] );
			IPSSearchRegistry::set('contextual.id'  , $this->request['cId'] );
		}
			
		/* What to do */
		switch( $this->request['do'] )
		{
			case 'active':
				$this->activeContent();
			break;
			
			case 'user_activity':
				$this->viewUserContent();
			break;
		
			case 'new_posts':
				$this->viewNewPosts();
			break;
			
			case 'search':
			case 'quick_search':
				$this->searchResults();
			break;
			
			default:
			case 'search_form':	
				$this->searchAdvancedForm();
			break;
		}
		
		/* Start session */
		$this->_endSession();
		
		/* If we have any HTML to print, do so... */
		$this->registry->output->setTitle( $this->title . ' - ' . ipsRegistry::$settings['board_name'] );
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Builds the advanced search form
	 *
	 * @access	public
	 * @param	string	Message
	 * @return	void
	 */
	public function searchAdvancedForm( $msg='', $removed_search_terms=array() )
	{
		/* Set up data */
		IPSSearchRegistry::set('view.search_form', true );
		
		/* Get any application specific filters */
		$appHtml   = $this->searchController->getHtml();
		$isBoolean = $this->searchController->isBoolean();
		
		/* Output */
		$this->title   = $this->lang->words['search_form'];
		$this->registry->output->addNavigation( $this->lang->words['search_form'], '' );
		$this->output .= $this->registry->output->getTemplate( 'search' )->searchAdvancedForm( $appHtml, $msg, $this->request['search_app'], $removed_search_terms, $isBoolean );
	}
	
	/**
	 * Processes a search request
	 *
	 * @access	public
	 * @return	void
	 */
	public function searchResults()
	{
		/* Search Term */
		$_st          = $this->searchController->formatSearchTerm( $this->request['search_term'] );
		$search_term  = $_st['search_term'];
		$removedTerms = $_st['removed'];
		
		/* Set up some defaults */
		$this->settings['max_search_word'] = $this->settings['max_search_word'] ? $this->settings['max_search_word'] : 300;
		
		/* Did we come in off a post request? */
		if ( $this->request['request_method'] == 'post' )
		{
			/* Set a no-expires header */
			$this->registry->getClass('output')->setCacheExpirationSeconds( 30 * 60 );
		}
		
		/* App specific */
		if ( isset( $this->request['search_sort_by_' . $this->request['search_app'] ] ) )
		{
			$this->request['search_sort_by']    = ( $_POST[ 'search_sort_by_' . $this->request['search_app'] ] ) ? $_POST[ 'search_sort_by_' . $this->request['search_app'] ] : $this->request['search_sort_by_' . $this->request['search_app'] ];
			$this->request['search_sort_order'] = ( $_POST[ 'search_sort_order_' . $this->request['search_app'] ] ) ? $_POST[ 'search_sort_order_' . $this->request['search_app'] ] : $this->request['search_sort_order_' . $this->request['search_app'] ];
		}
		
		/* Populate the registry */
		IPSSearchRegistry::set('in.search_app'		 , $this->request['search_app'] );
		IPSSearchRegistry::set('in.raw_search_term'  , trim( $this->request['search_term'] ) );
		IPSSearchRegistry::set('in.clean_search_term', $search_term );
		IPSSearchRegistry::set('in.search_higlight'  , str_replace( '.', '', $this->request['search_term'] ) );
		IPSSearchRegistry::set('in.search_date_end'  , ( $this->request['search_date_start'] && $this->request['search_date_end'] )  ? $this->request['search_date_end'] : 'now' );
		IPSSearchRegistry::set('in.search_date_start', ( $this->request['search_date_start']  )  ? $this->request['search_date_start'] : '' );
		IPSSearchRegistry::set('in.search_author'    , ( isset( $this->request['search_author'] ) && $this->request['search_author'] ) ? $this->request['search_author'] : '' );
		
		/* Set sort filters */
		$this->_setSortFilters();
		
		/* These can be overridden in the actual engine scripts */
	//	IPSSearchRegistry::set('set.hardLimit'        , 0 );
		IPSSearchRegistry::set('set.resultsCutToLimit', false );
		IPSSearchRegistry::set('set.resultsAsForum'   , false );
		
		/* Are we option to show titles only / search in titles only */
		IPSSearchRegistry::set('opt.searchTitleOnly', ( isset( $this->request['content_title_only'] ) && $this->request['content_title_only']  ) ? true : false );
		IPSSearchRegistry::set('display.onlyTitles' , ( ( $this->request['show_as_titles'] AND $this->settings['enable_show_as_titles'] ) OR ( IPSSearchRegistry::get('opt.searchTitleOnly') ) ) ? true : false );
		
		/* Time check */
		if ( IPSSearchRegistry::get('in.search_date_start') AND strtotime( IPSSearchRegistry::get('in.search_date_start') ) > time() )
		{
			IPSSearchRegistry::set('in.search_date_start', 'now' );
		}
		
		if ( IPSSearchRegistry::get('in.search_date_end') AND strtotime( IPSSearchRegistry::get('in.search_date_end') ) > time() )
		{
			IPSSearchRegistry::set('in.search_date_end', 'now' );
		}
		
		/* Do some date checking */
		if( IPSSearchRegistry::get('in.search_date_end') AND IPSSearchRegistry::get('in.search_date_start') AND strtotime( IPSSearchRegistry::get('in.search_date_start') ) > strtotime( IPSSearchRegistry::get('in.search_date_end') ) )
		{
			$this->searchAdvancedForm( $this->lang->words['search_invalid_date_range'] );
			return;	
		}
		
		/**
		 * Ok this is an upper limit.
		 * If you needed to change this, you could do so via conf_global.php by adding:
		 * $INFO['max_search_word'] = #####;
		 */
		if ( $this->settings['min_search_word'] && ! IPSSearchRegistry::get('in.search_author') )
		{
			$_words	= explode( ' ', IPSSearchRegistry::get('in.raw_search_term') );
			$_ok	= true;
			
			foreach( $_words as $_word )
			{
				if( strlen( $_word ) < $this->settings['min_search_word'] )
				{
					$_ok	= false;
					break;
				}
			}

			if( !$_ok )
			{
				$this->searchAdvancedForm( sprintf( $this->lang->words['search_term_short'], $this->settings['min_search_word'] ), $removedTerms );
				return;
			}
		}
		
		if ( $this->settings['max_search_word'] && strlen( IPSSearchRegistry::get('in.raw_search_term') ) > $this->settings['max_search_word'] )
		{
			$this->searchAdvancedForm( sprintf( $this->lang->words['search_term_long'], $this->settings['max_search_word'] ) );
			return;
		}
		
		/* Search Flood Check */
		if( $this->memberData['g_search_flood'] )
		{
			/* Check for a cookie */
			$last_search = IPSCookie::get( 'sfc' );
			$last_term	= str_replace( "&quot;", '"', IPSCookie::get( 'sfct' ) );
			$last_term	= str_replace( "&amp;", '&',  $last_term );			
			
			/* If we have a last search time, check it */
			if( $last_search && $last_term )
			{
				if( ( time() - $last_search ) <= $this->memberData['g_search_flood'] && $last_term != IPSSearchRegistry::get('in.raw_search_term') )
				{
					$this->searchAdvancedForm( sprintf( $this->lang->words['xml_flood'], $this->memberData['g_search_flood'] ) );
					return;					
				}
				else
				{
					/* Reset the cookie */
					IPSCookie::set( 'sfc', time() );
					IPSCookie::set( 'sfct', urlencode( IPSSearchRegistry::get('in.raw_search_term') ) );
				}
			}
			/* Set the cookie */
			else
			{
				IPSCookie::set( 'sfc', time() );
				IPSCookie::set( 'sfct', urlencode( IPSSearchRegistry::get('in.raw_search_term') ) );
			}
		}
		
		/* Clean search term for results view */
		$_search_term = trim( preg_replace( "#(^|\s)(\+|\-|\||\~)#", " ", $search_term ) );
		
		/* Can we do this? */
		if ( IPSLib::appisSearchable( IPSSearchRegistry::get('in.search_app'), 'search' ) )
		{
			/* Perform the search */
			$this->searchController->search();
			
			/* Get count */
			$count = $this->searchController->getResultCount();
			
			/* Get results which will be array of IDs */
			$results = $this->searchController->getResultSet();
			
			/* Get templates to use */
			$template = $this->searchController->fetchTemplates();
						
			/* Fetch sort details */
			$sortDropDown = $this->searchController->fetchSortDropDown();
			
			/* Fetch sort details */
			$sortIn       = $this->searchController->fetchSortIn();
			
			/* Build pagination */
			$links = $this->registry->output->generatePagination( array( 'totalItems'		=> $count,
																		'itemsPerPage'		=> IPSSearchRegistry::get('opt.search_per_page'),
																		'currentStartValue'	=> IPSSearchRegistry::get('in.start'),
																		'baseUrl'			=> $this->_buildURLString() . '&amp;search_app=' . IPSSearchRegistry::get('in.search_app') . '' )	);
	
			/* Showing */
			$showing = array( 'start' => IPSSearchRegistry::get('in.start') + 1, 'end' => ( IPSSearchRegistry::get('in.start') + IPSSearchRegistry::get('opt.search_per_page') ) > $count ? $count : IPSSearchRegistry::get('in.start') + IPSSearchRegistry::get('opt.search_per_page') );
						
			/* Parse result set */
			$results = $this->registry->output->getTemplate( $template['group'] )->$template['template']( $results, ( IPSSearchRegistry::get('display.onlyTitles') || IPSSearchRegistry::get( 'opt.noPostPreview') ) ? 1 : 0 );
		}
		else
		{
			$count   = 0;
			$results = array();
		}
		
		/* Output */
		$this->title   = $this->lang->words['search_results'];
		$this->output .= $this->registry->output->getTemplate( 'search' )->searchResultsWrapper( $results, $sortDropDown, $sortIn, $links, $count, $showing, $_search_term, $this->_buildURLString(), $this->request['search_app'], $removed_search_terms[0], IPSSearchRegistry::get('set.hardLimit'), IPSSearchRegistry::get('set.resultsCutToLimit') );
	}
	
	/**
	 * Starts session
	 * Loads / creates a session based on activity
	 *
	 * @access	protected
	 * @return
	 */
	protected function _startSession()
	{
		$session_id  = IPSText::md5Clean( $this->request['sid'] );
		$requestType = ( $this->request['request_method'] == 'post' ) ? 'post' : 'get';
		
		if ( $session_id )
		{
			/* We check on member id 'cos we can. Obviously guests will have a member ID of zero, but meh */
			$this->_session = $this->DB->buildAndFetch( array( 'select' => '*',
															   'from'   => 'search_sessions',
															   'where'  => 'session_id=\'' . $session_id . '\' AND session_member_id=' . $this->memberData['member_id'] ) );
		}
		
		/* Deflate */
		if ( $this->_session['session_id'] )
		{
			if ( $this->_session['session_data'] )
			{
				$this->_session['_session_data'] = unserialize( $this->_session['session_data'] );
				
				if ( isset( $this->_session['_session_data']['search_app_filters'] ) )
				{
					$this->request['search_app_filters'] = $this->_session['_session_data']['search_app_filters'];
				}
			}
			
			IPSDebug::addMessage( "Loaded search session: <pre>" . var_export( $this->_session['_session_data'], true ) . "</pre>" );
		}
		else
		{
			/* Create a session */
			$this->_session = array( 'session_id'        => md5( uniqid( microtime(), true ) ),
									 'session_created'   => time(),
									 'session_updated'   => time(),
									 'session_member_id' => $this->memberData['member_id'],
									 'session_data'      => serialize( array( 'search_app_filters' => $this->request['search_app_filters'] ) ) );
									 
			$this->DB->insert( 'search_sessions', $this->_session );
			
			$this->_session['_session_data']['search_app_filters'] = $this->request['search_app_filters'];
			
			IPSDebug::addMessage( "Created search session: <pre>" . var_export( $this->_session['_session_data'], true ) . "</pre>" );
		}
		
		/* Do we have POST infos? */
		if ( isset( $_POST['search_app_filters'] ) )
		{
			$this->_session['_session_data']['search_app_filters'] = ( is_array( $this->_session['_session_data']['search_app_filters'] ) ) ? IPSLib::arrayMergeRecursive( $this->_session['_session_data']['search_app_filters'], $_POST['search_app_filters'] ) : $_POST['search_app_filters'];
			$this->request['search_app_filters']                   = $this->_session['_session_data']['search_app_filters'];
			
			IPSDebug::addMessage( "Updated filters: <pre>" . var_export( $_POST['search_app_filters'], true ) . "</pre>" );
		}
		
		/* Globalize the session ID */
		$this->request['_sid'] = $this->_session['session_id'];
	}
	
	/**
	 * End the session
	 *
	 */
	protected function _endSession()
	{
		if ( $this->_session['session_id'] )
		{
			$sd = array( 'session_updated'   => time(),
						 'session_data'      => serialize( $this->_session['_session_data'] ) );
						 
			$this->DB->update( 'search_sessions', $sd, 'session_id=\'' . $this->_session['session_id'] . '\'' );
		}
		
		/* Delete old sessions */
		$this->DB->delete( 'search_sessions', 'session_updated < ' . ( time() - 86400 ) );
	}
	
	/**
	 * Set the search order and key
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _setSortFilters()
	{
		$app = $this->request['search_app'];
		$key = 'date';
		$dir = 'desc';
		$dun = false;
		
		/* multi search in options? */
		if ( isset( $this->request['search_app_filters'][ $app ]['searchInKey'] ) )
		{
			$_k = $this->request['search_app_filters'][ $app ]['searchInKey'];
			
			if ( isset( $this->request['search_app_filters'][ $app ][ $_k ]['sortKey'] ) )
			{
				$dun = true;
				$key = $this->request['search_app_filters'][ $app ][ $_k ]['sortKey'];
				$dir = $this->request['search_app_filters'][ $app ][ $_k ]['sortDir'];
			}
		}
		
		/* Normal options - although sometimes used even with multiple types */
		if ( ! $dun AND isset( $this->request['search_app_filters'][$app]['sortKey'] ) )
		{
			$key = $this->request['search_app_filters'][$app]['sortKey'];
			$dir = $this->request['search_app_filters'][$app]['sortDir'];
		}
		/* Global */
		else
		{
			if ( isset( $this->request['search_sort_by'] ) )
			{
				$key = $this->request['search_sort_by'];
				$dir = $this->request['search_sort_order'];
			}
		}
	
		/* Numeric? */
		if ( is_numeric( $dir ) )
		{
			$dir = ( $dir == 0 ) ? 'desc' : 'asc';
		}
		
		IPSSearchRegistry::set('in.search_sort_by'   , trim( $key ) );
		IPSSearchRegistry::set('in.search_sort_order', ( $dir != 'desc' ) ? 'asc' : 'desc' );
	}
	
	/**
	 * Displays the active topics screen
	 *
	 * @access	public
	 * @return	void
	 */
	public function activeContent()
	{
		IPSSearchRegistry::set('in.search_app', $this->request['search_app'] );
		IPSSearchRegistry::set('in.period'    , ( empty( $this->request['period'] ) ) ? 'today' : $this->request['period'] );
		
		/* Can we do this? */
		if ( IPSLib::appisSearchable( IPSSearchRegistry::get('in.search_app'), 'active' ) )
		{
			/* Perform the search */
			$this->searchController->viewActiveContent();
			
			/* Get count */
			$count = $this->searchController->getResultCount();
			
			/* Get results which will be array of IDs */
			$results = $this->searchController->getResultSet();
			
			/* Get templates to use */
			$template = $this->searchController->fetchTemplates();
			
			/* Fetch sort details */
			$sortIn       = $this->searchController->fetchSortIn();
			
			/* Parse result set */
			$results = $this->registry->output->getTemplate( $template['group'] )->$template['template']( $results, ( IPSSearchRegistry::get('display.onlyTitles') || IPSSearchRegistry::get( 'opt.noPostPreview') ) ? 1 : 0 );
			
			/* Build pagination */
			$links = $this->registry->output->generatePagination( array( 'totalItems'		=> $count,
																		'itemsPerPage'		=> IPSSearchRegistry::get('opt.search_per_page'),
																		'currentStartValue'	=> IPSSearchRegistry::get('in.start'),
																		'baseUrl'			=> 'app=core&amp;module=search&amp;do=active&amp;period=' . IPSSearchRegistry::get('in.period') . '&amp;search_app=' . IPSSearchRegistry::get('in.search_app') . '&amp;sid=' . $this->request['_sid'] ) );
		}
		else
		{
			$count   = 0;
			$results = array();
		}
		
		/* Output */
		$this->title   = $this->lang->words['active_posts_title'];
		$this->registry->output->addNavigation( $this->lang->words['active_posts_title'], '' );
		$this->output .= $this->registry->output->getTemplate( 'search' )->activePostsView( $results, $links, $count, $sortIn );
	}
	
	/**
	 * Displays latest user content
	 *
	 * @access	public
	 * @return	void
	 */
	public function viewUserContent()
	{
		/* INIT */
		$id 	    = $this->request['mid'] ? intval( trim( $this->request['mid'] ) ) : $this->memberData['member_id'];
		$member	    = IPSMember::load( $id, 'core' );
		$beginStamp = 0;
		
		if ( ! $member['member_id'] )
		{
			$this->registry->output->showError( 'search_invalid_id', 10147, null, null, 403 );
		}
		
		IPSSearchRegistry::set('in.search_app', $this->request['search_app'] );
		IPSSearchRegistry::set('in.userMode'  , ( $this->request['userMode'] ) ? $this->request['userMode'] : 'all' );
		
		/* Can we do this? */
		if ( IPSLib::appisSearchable( IPSSearchRegistry::get('in.search_app'), 'usercontent' ) )
		{
			/* Perform the search */
			$this->searchController->viewUserContent( $member );
			
			/* Get count */
			$count = $this->searchController->getResultCount();
			
			/* Get results which will be array of IDs */
			$results = $this->searchController->getResultSet();
			
			/* Get templates to use */
			$template = $this->searchController->fetchTemplates();
			
			/* Fetch sort details */
			$sortIn       = $this->searchController->fetchSortIn();
			
			/* Parse result set */
			$results = $this->registry->output->getTemplate( $template['group'] )->$template['template']( $results, ( IPSSearchRegistry::get('display.onlyTitles') || IPSSearchRegistry::get( 'opt.noPostPreview') ) ? 1 : 0 );
			
			/* Build pagination */
			$links = $this->registry->output->generatePagination( array( 'totalItems'		=> $count,
																		'itemsPerPage'		=> IPSSearchRegistry::get('opt.search_per_page'),
																		'currentStartValue'	=> IPSSearchRegistry::get('in.start'),
																		'baseUrl'			=> 'app=core&amp;module=search&amp;do=user_activity&amp;mid=' . $id . '&amp;search_app=' . IPSSearchRegistry::get('in.search_app') . '&amp;userMode=' . IPSSearchRegistry::get('in.userMode')  . '&amp;sid=' . $this->request['_sid'] ) );
		}
		else
		{
			$count   = 0;
			$results = array();
		}
		
		$this->title   = sprintf( $this->lang->words['s_participation_title'], $member['members_display_name'] );
		$this->registry->output->addNavigation( $this->title, '' );
		$this->output .= $this->registry->output->getTemplate( 'search' )->userPostsView( $results, $links, $count, $member, IPSSearchRegistry::get('set.hardLimit'), IPSSearchRegistry::get('set.resultsCutToLimit'), $beginStamp, $sortIn );
	}
	
	/**
	 * View new posts since your last visit
	 *
	 * @access	public
	 * @return	void
	 */
	public function viewNewPosts()
	{	
		IPSSearchRegistry::set('in.search_app', $this->request['search_app'] );
		
		/* Can we do this? */
		if ( IPSLib::appisSearchable( IPSSearchRegistry::get('in.search_app'), 'vnc' ) )
		{
			/* Perform the search */
			$this->searchController->viewNewContent();
			
			/* Get count */
			$count = $this->searchController->getResultCount();
			
			/* Get results which will be array of IDs */
			$results = $this->searchController->getResultSet();
			
			/* Get templates to use */
			$template = $this->searchController->fetchTemplates();
			
			/* Fetch sort details */
			$sortDropDown = $this->searchController->fetchSortDropDown();
			
			/* Fetch sort details */
			$sortIn       = $this->searchController->fetchSortIn();
			
			/* Parse result set */
			$results = $this->registry->output->getTemplate( $template['group'] )->$template['template']( $results, ( IPSSearchRegistry::get('display.onlyTitles') || IPSSearchRegistry::get( 'opt.noPostPreview') ) ? 1 : 0 );
			
			/* Build pagination */
			$links = $this->registry->output->generatePagination( array( 'totalItems'		=> $count,
																		'itemsPerPage'		=> IPSSearchRegistry::get('opt.search_per_page'),
																		'currentStartValue'	=> IPSSearchRegistry::get('in.start'),
																		'baseUrl'			=> 'app=core&amp;module=search&amp;do=new_posts&amp;search_app=' . IPSSearchRegistry::get('in.search_app')  . '&amp;sid= ' . $this->request['_sid'] ) );
	
			/* Showing */
			$showing = array( 'start' => IPSSearchRegistry::get('in.start') + 1, 'end' => ( IPSSearchRegistry::get('in.start') + IPSSearchRegistry::get('opt.search_per_page') ) > $count ? $count : IPSSearchRegistry::get('in.start') + IPSSearchRegistry::get('opt.search_per_page') );
		}
		else
		{
			$count   = 0;
			$results = array();
		}
		
		/* Output */
		$this->title   = $this->lang->words['new_posts_title'];
		$this->registry->output->addNavigation( $this->lang->words['new_posts_title'], '' );
		$this->output .= $this->registry->output->getTemplate( 'search' )->newPostsView( $results, $links, $count, $sortDropDown, $sortIn, IPSSearchRegistry::get('set.resultCutToDate') );
	}
	

	/**
	 * Returns a url string that will maintain search results via links
	 *
	 * @access	private
	 * @return	string
	 */
	private function _buildURLString()
	{
		/* INI */
		$url_string  = 'app=core&amp;module=search&amp;do=search&amp;andor_type=' . $this->request['andor_type'];
		$url_string .= '&amp;sid=' . $this->request['_sid'];
		
		/* Add author name */
		if( isset( $this->request['search_author'] ) AND $this->request['search_author'] )
		{
			$url_string .= "&amp;search_author=" . urlencode($this->request['search_author']);
		}
		
		/* Add titles only */
		if( isset( $this->request['show_as_titles'] ) AND $this->request['show_as_titles'] )
		{
			$url_string .= "&amp;show_as_titles={$this->request['show_as_titles']}";
		}
		
		/* Search Range */
		if( isset( $this->request['search_date_start'] ) AND $this->request['search_date_start'] )
		{
			$url_string .= "&amp;search_date_start={$this->request['search_date_start']}";
		}
		
		if( isset( $this->request['search_date_end'] ) AND $this->request['search_date_end'] )
		{
			$url_string .= "&amp;search_date_end={$this->request['search_date_end']}";
		}
		
		if ( IPSSearchRegistry::get('contextual.type') )
		{
			$url_string .= "&amp;cType=" . IPSSearchRegistry::get('contextual.type') . "&amp;cId=" . IPSSearchRegistry::get('contextual.id');
		}
			
		/* Search app filters */
		/*if( isset( $this->request['search_app_filters'] ) && count( $this->request['search_app_filters'] ) )
		{
			foreach( $this->request['search_app_filters'] as $app => $filter_data )
			{
				if( is_array( $filter_data ) )
				{					
					foreach( $filter_data as $k => $v )
					{
						if ( is_array( $v ) )
						{
							foreach( $v as $_k => $_v )
							{
								if ( $_v != '' )
								{
									$url_string .= "&amp;search_app_filters[{$app}][{$k}][$_k]={$_v}";
								}
							}
						}
						else if ( $v != '' )
						{
							$url_string .= "&amp;search_app_filters[{$app}][{$k}]={$v}";
						}
					}
				}
				else if ( $v != '' )
				{
					$url_string .= "&amp;search_app_filters[{$app}]={$v}";
				}
			}
		}*/

		if( isset( $this->request['content_title_only'] ) && $this->request['content_title_only'] )
		{
			$url_string .= "&amp;content_title_only=1";
		}
		
		if( isset( $this->request['type'] ) && isset( $this->request['type_id'] ) )
		{
			$url_string .= "&amp;type={$this->request['type']}&amp;type_id={$this->request['type_id']}";
		}
		
		if( isset( $this->request['type_2'] ) && isset( $this->request['type_id_2'] ) )
		{
			$url_string .= "&amp;type_2={$this->request['type_2']}&amp;type_id_2={$this->request['type_id_2']}";
		}
		
		/* Fix up the search term a bit */
		$_search_term = str_replace( '&amp;', '&', $this->request['search_term'] );
		$_search_term = str_replace( '&quot;', '"', $_search_term );
		$_search_term = str_replace( '&gt;', '>', $_search_term );
		$_search_term = str_replace( '&lt;', '<', $_search_term );
		$_search_term = str_replace( '&#036;', '$', $_search_term );

		$url_string .= '&amp;search_term=' . urlencode( $_search_term );

		return $url_string;		
	}
	
	/**
	 * Checks to see if the logged in user is allowed to use the search system
	 *
	 * @access	private
	 * @return	void
	 */
	private function _canSearch()
	{
		/* Check the search setting */
		if( ! $this->settings['allow_search'] )
		{
			if( $this->xml_out )
			{
				@header( "Content-type: text/html;charset={$this->settings['gb_char_set']}" );
				print $this->lang->words['search_off'];
				exit();
			}
			else
			{
				$this->registry->output->showError( 'search_off', 10145 );
			}
		}
		
		/* Check the member authorization */
		if( ! isset( $this->memberData['g_use_search'] ) || ! $this->memberData['g_use_search'] )
		{
			if( $this->xml_out )
			{
				@header( "Content-type: text/html;charset={$this->settings['gb_char_set']}" );
				print $this->lang->words['no_xml_permission'];
				exit();
			}
			else
			{
				$this->registry->output->showError( 'no_permission_to_search', 10146, null, null, 403 );
			}
		}		
	} 
}