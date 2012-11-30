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

class search_engine_forums extends search_engine
{
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry )
	{
		/* Hard limit */
		IPSSearchRegistry::set('set.hardLimit', ( ipsRegistry::$settings['search_hardlimit'] ) ? ipsRegistry::$settings['search_hardlimit'] : 200 );
		
		/* Get class forums, used for displaying forum names on results */
		if ( ipsRegistry::isClassLoaded('class_forums') !== TRUE )
		{
			require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php" );
			ipsRegistry::setClass( 'class_forums', new class_forums( ipsRegistry::instance() ) );
			ipsRegistry::getClass( 'class_forums' )->forumsInit();
		}
		
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
		$start		        = intval( IPSSearchRegistry::get('in.start') );
		$perPage            = IPSSearchRegistry::get('opt.search_per_page');
		$sort_by            = IPSSearchRegistry::get('in.search_sort_by');
		$sort_order         = IPSSearchRegistry::get('in.search_sort_order');
		$search_term        = IPSSearchRegistry::get('in.clean_search_term');
		$content_title_only = IPSSearchRegistry::get('opt.searchTitleOnly');
		$post_search_only   = IPSSearchRegistry::get('opt.onlySearchPosts');
		$app			    = IPSSearchRegistry::get('in.search_app');
		$search_ids		    = array();
		$groupby			= false;
		$cType              = IPSSearchRegistry::get('contextual.type');
		$cId		        = IPSSearchRegistry::get('contextual.id' );
		
		/* Contextual search */
		if ( $cType == 'topic' )
		{
			$content_title_only = false;
			$post_search_only   = true;
			
			IPSSearchRegistry::set('opt.searchTitleOnly', false);
			IPSSearchRegistry::set('opt.onlySearchPosts', true);
			IPSSearchRegistry::set('display.onlyTitles', false);
			IPSSearchRegistry::set('opt.noPostPreview', false);
		}
		
		/* Permissions */
		$permissions['TopicSoftDeleteSee']  = $this->registry->getClass('class_forums')->canSeeSoftDeletedTopics( 0 );
		$permissions['canQueue']			= $this->registry->getClass('class_forums')->canQueuePosts( 0 );
		$permissions['PostSoftDeleteSee']   = $this->registry->getClass('class_forums')->canSeeSoftDeletedPosts( 0 );
		$permissions['SoftDeleteReason']    = $this->registry->getClass('class_forums')->canSeeSoftDeleteReason( 0 );
		$permissions['SoftDeleteContent']   = $this->registry->getClass('class_forums')->canSeeSoftDeleteContent( 0 );
		
		/* Sorting */
		switch( $sort_by )
		{
			default:
			case 'date':
				$sortKey     = 'last_post';
				$sortKeyPost = 'post_date';
			break;
			case 'title':
				$sortKeyPost = $sortKey  = 'tordinal';
			break;
			case 'posts':
				$sortKeyPost = $sortKey  = 'posts';
			break;
			case 'views':
				$sortKeyPost = $sortKey  = 'views';
			break;
		}
					
		/* Limit Results */
		$this->sphinxClient->SetLimits( intval($start), intval($perPage) );
		
		/* Loop through the forums and build a list of forums we're allowed access to */
		$forumIdsOk  = array();
		$forumIdsBad = array();
		
		if ( ! empty( ipsRegistry::$request['search_app_filters']['forums']['forums'] ) AND count( ipsRegistry::$request['search_app_filters']['forums']['forums'] ) )
		{
			foreach(  ipsRegistry::$request['search_app_filters']['forums']['forums'] as $forum_id )
			{
				if( $forum_id )
				{
					$data	= $this->registry->class_forums->forum_by_id[ $forum_id ];
					
					/* Check for sub forums */
					$children = ipsRegistry::getClass( 'class_forums' )->forumsGetChildren( $forum_id );
					
					foreach( $children as $kid )
					{
						if( ! in_array( $kid, ipsRegistry::$request['search_app_filters']['forums']['forums'] ) )
						{
							if( ! $this->registry->permissions->check( 'read', $this->registry->class_forums->forum_by_id[ $kid ] ) )
							{
								$forumIdsBad[] = $kid;
								continue;
							}
							
							/* Can read, but is it password protected, etc? */
							if ( ! $this->registry->class_forums->forumsCheckAccess( $kid, 0, 'forum', array(), true ) )
							{
								$forumIdsBad[] = $kid;
								continue;
							}

							if ( ! $this->registry->class_forums->forum_by_id[ $kid ]['sub_can_post'] OR ! $this->registry->class_forums->forum_by_id[ $kid ]['can_view_others'] )
							{
								$forumIdsBad[] = $kid;
								continue;
							}
							
							$forumIdsOk[] = $kid;
						}
					}

					/* Can we read? */
					if ( ! $this->registry->permissions->check( 'view', $data ) )
					{
						$forumIdsBad[] = $forum_id;
						continue;
					}

					/* Can read, but is it password protected, etc? */
					if ( ! $this->registry->class_forums->forumsCheckAccess( $forum_id, 0, 'forum', array(), true ) )
					{
						$forumIdsBad[] = $forum_id;
						continue;
					}

					if ( ! $data['sub_can_post'] OR ! $data['can_view_others'] AND !$this->memberData['g_access_cp'] )
					{
						$forumIdsBad[] = $forum_id;
						continue;
					}
				
					$forumIdsOk[] = $forum_id;
				}
			}
		}
		
		if ( ! count($forumIdsOk) )
		{
			/* Get list of good forum IDs */
			$forumIdsOk = $this->registry->class_forums->fetchSearchableForumIds();
		}
		
		/* Add allowed forums */
		if ( $noForums !== true )
		{
			$forumIdsOk = ( count( $forumIdsOk ) ) ? $forumIdsOk : array( 0 => 0 );
			
			/* Contextual */
			if ( $cType == 'forum' AND $cId AND in_array( $cId, $forumIdsOk ) )
			{
				$this->sphinxClient->SetFilter( 'forum_id', array( $cId ) );
			}
			else
			{
				$this->sphinxClient->SetFilter( 'forum_id', $forumIdsOk );
			}
		}
		
		/* Topic contextual */
		if ( $cType == 'topic' AND $cId )
		{
			$this->sphinxClient->SetFilter( 'tid', array( $cId ) );
		}
	
		/* Exclude some items */
		if ( ! $content_title_only )
		{
			if ( $permissions['SoftDeleteContent'] AND $permissions['PostSoftDeleteSee'] AND $permissions['canQueue'] )
			{
				$this->sphinxClient->SetFilter( 'queued', array( 0,1,2 ) );
			}
			else if ( $permissions['SoftDeleteContent'] AND $permissions['PostSoftDeleteSee'] )
			{
				$this->sphinxClient->SetFilter( 'queued', array( 0,2 ) );
			}
			else if ( $permissions['canQueue'] )
			{
				$this->sphinxClient->SetFilter( 'queued', array( 0,1 ) );
			}
			else
			{
				$this->sphinxClient->SetFilter( 'queued', array( 0 ) );
			}
		}
		
		if ( $permissions['SoftDeleteContent'] AND $permissions['TopicSoftDeleteSee'] AND $permissions['canQueue'] )
		{
			$this->sphinxClient->SetFilter( 'approved'    , array( 0,1 ) );
			$this->sphinxClient->SetFilter( 'soft_deleted', array( 0,1 ) );
		}
		else if ( $permissions['SoftDeleteContent'] AND $permissions['TopicSoftDeleteSee'] )
		{
			$this->sphinxClient->SetFilter( 'approved', array( 1 ) );
			$this->sphinxClient->SetFilter( 'soft_deleted', array( 0,1 ) );
		}
		else if ( $permissions['canQueue'] )
		{
			$this->sphinxClient->SetFilter( 'soft_deleted', array( 0 ) );
			$this->sphinxClient->SetFilter( 'approved', array( 0,1 ) );
		}
		else
		{
			$this->sphinxClient->SetFilter( 'soft_deleted', array( 0 ) );
			$this->sphinxClient->SetFilter( 'approved', array( 1 ) );
		}
		

		/* Additional filters */
		if ( IPSSearchRegistry::get('opt.pCount') )
		{
			$this->sphinxClient->SetFilterRange( 'posts', intval( IPSSearchRegistry::get('opt.pCount') ), 1500000000 );
		}
		
		if ( IPSSearchRegistry::get('opt.pViews') )
		{
			$this->sphinxClient->SetFilterRange( 'views', intval( IPSSearchRegistry::get('opt.pViews') ), 1500000000 );
		}
		
		/* Date limit */
		if ( $this->search_begin_timestamp )
		{
			if ( ! $this->search_end_timestamp )
			{
				$this->search_end_timestamp = time() + 100;
			}
			
			if ( $content_title_only )
			{
				$this->sphinxClient->SetFilterRange( 'start_date', $this->search_begin_timestamp, $this->search_end_timestamp );
			}
			else
			{
				$this->sphinxClient->SetFilterRange( 'post_date', $this->search_begin_timestamp, $this->search_end_timestamp );
			}
		}
	
		if ( IPSSearchRegistry::get('opt.noPostPreview') OR IPSSearchRegistry::get('display.onlyTitles') )
		{
			$groupby = true;
		}
	
		if ( $content_title_only )
		{
			if ( $sort_order == 'asc' )
			{
				$this->sphinxClient->SetSortMode( SPH_SORT_ATTR_ASC, $sortKey );
			}
			else
			{
				$this->sphinxClient->SetSortMode( SPH_SORT_ATTR_DESC, $sortKey );
			}
			
			if ( $groupby )
			{
				$this->sphinxClient->SetGroupBy( 'last_post_group', SPH_GROUPBY_ATTR, '@group ' . $sort_order );
			}
			
			$_s = ( $search_term ) ? '@title ' . $search_term : '';
			
			$result = $this->sphinxClient->Query( $_s, 'forums_search_posts_main,forums_search_posts_delta' );
			
			$this->logSphinxWarnings();
		}
		else
		{
			if ( $groupby )
			{
				$this->sphinxClient->SetGroupBy( 'last_post_group', SPH_GROUPBY_ATTR, '@group DESC');
			}
			
			if ( $sort_order == 'asc' )
			{
				$this->sphinxClient->SetSortMode( SPH_SORT_ATTR_ASC, $sortKeyPost );
			}
			else
			{
				$this->sphinxClient->SetSortMode( SPH_SORT_ATTR_DESC, $sortKeyPost );
			}
			
			if ( $post_search_only )
			{
				$_s = ( $search_term ) ? '@post ' . $search_term : '';
				$result = $this->sphinxClient->Query(  $_s, 'forums_search_posts_main,forums_search_posts_delta' );
			}
			else
			{
				$_s = ( $search_term AND strstr( $search_term, '"' ) ) ? '@post  ' . $search_term . ' | @title ' . $search_term : ( $search_term ? '@(post,title) ' . $search_term : '' );
				$result = $this->sphinxClient->Query(  $_s, 'forums_search_posts_main,forums_search_posts_delta' );
			}
			
			$this->logSphinxWarnings();
		}
		
		if ( is_array( $result['matches'] ) && count( $result['matches'] ) )
		{
			foreach( $result['matches'] as $res )
			{
				$search_ids[] = ( $content_title_only ) ? $res['attrs']['tid'] : $res['attrs']['search_id'];
			}
		}

		/* Return it */
		return array( 'count' => intval( $result['total_found'] ) > 1000 ? 1000 : $result['total_found'], 'resultSet' => $search_ids );
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
		/* Ensure we limit by date */
		$this->settings['search_ucontent_days'] = ( $this->settings['search_ucontent_days'] ) ? $this->settings['search_ucontent_days'] : 365;
	
		/* Bit of init */
		$app  = IPSSearchRegistry::get('in.search_app');
		$time = time() - ( 86400 * intval( $this->settings['search_ucontent_days'] ) );
		
		IPSSearchRegistry::set('in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set('in.search_sort_order', 'desc' );
		
		switch( IPSSearchRegistry::get('in.userMode') )
		{
			default:
			case 'all': 
				IPSSearchRegistry::set('opt.searchTitleOnly', false );
				IPSSearchRegistry::set('display.onlyTitles' , true );
				IPSSearchRegistry::set('opt.noPostPreview'  , false );
			break;
			case 'title': 
				IPSSearchRegistry::set('opt.searchTitleOnly', true );
				IPSSearchRegistry::set('display.onlyTitles' , true );
				IPSSearchRegistry::set('opt.noPostPreview'  , false );
			break;	
			case 'content': 
				IPSSearchRegistry::set('opt.searchTitleOnly', false );
				IPSSearchRegistry::set('display.onlyTitles' , false );
				IPSSearchRegistry::set('opt.noPostPreview'  , false );
			break;	
		}
		
		/* Init */
		$start		= IPSSearchRegistry::get('in.start');
		$perPage    = IPSSearchRegistry::get('opt.search_per_page');
	
		IPSSearchRegistry::set('in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set('in.search_sort_order', 'desc' );
		
		/* Limit Results */
		$this->sphinxClient->SetLimits( intval($start), intval($perPage) );
		
		/* Get list of good forum IDs */
		$forumIdsOk	= $this->registry->class_forums->fetchSearchableForumIds( $this->memberData['member_id'] );
		
		$this->sphinxClient->SetFilter( 'forum_id', $forumIdsOk );
		
		/* what we doing? */
		if ( IPSSearchRegistry::get('in.userMode') != 'title' )
		{
			$this->sphinxClient->SetFilter( 'author_id', array( intval( $member['member_id'] ) ) );
		}
		else
		{
			$this->sphinxClient->SetFilter( 'starter_id', array( intval( $member['member_id'] ) ) );
		}
		
		if ( $this->settings['search_ucontent_days'] )
		{
			$this->sphinxClient->SetFilterRange( 'post_date', $time, time() );
		}
				
		/* Set up perms */
		$permissions['TopicSoftDeleteSee']  = $this->registry->getClass('class_forums')->canSeeSoftDeletedTopics( 0 );
		$permissions['canQueue']			= $this->registry->getClass('class_forums')->canQueuePosts( 0 );
		$permissions['PostSoftDeleteSee']   = $this->registry->getClass('class_forums')->canSeeSoftDeletedPosts( 0 );
		$permissions['SoftDeleteReason']    = $this->registry->getClass('class_forums')->canSeeSoftDeleteReason( 0 );
		$permissions['SoftDeleteContent']   = $this->registry->getClass('class_forums')->canSeeSoftDeleteContent( 0 );
		
		/* Exclude some items */
		if ( IPSSearchRegistry::get('in.userMode') != 'title' )
		{
			if ( $permissions['SoftDeleteContent'] AND $permissions['PostSoftDeleteSee'] AND $permissions['canQueue'] )
			{
				$this->sphinxClient->SetFilter( 'queued', array( 0,1,2 ) );
			}
			else if ( $permissions['SoftDeleteContent'] AND $permissions['PostSoftDeleteSee'] )
			{
				$this->sphinxClient->SetFilter( 'queued', array( 0,2 ) );
			}
			else if ( $permissions['canQueue'] )
			{
				$this->sphinxClient->SetFilter( 'queued', array( 0,1 ) );
			}
			else
			{
				$this->sphinxClient->SetFilter( 'queued', array( 0 ) );
			}
		}
		else
		{
			if ( $permissions['SoftDeleteContent'] AND $permissions['TopicSoftDeleteSee'] AND $permissions['canQueue'] )
			{
				$this->sphinxClient->SetFilter( 'approved'    , array( 0,1 ) );
				$this->sphinxClient->SetFilter( 'soft_deleted', array( 0,1 ) );
			}
			else if ( $permissions['SoftDeleteContent'] AND $permissions['TopicSoftDeleteSee'] )
			{
				$this->sphinxClient->SetFilter( 'approved', array( 0 ) );
				$this->sphinxClient->SetFilter( 'soft_deleted', array( 0,1 ) );
			}
			else if ( $permissions['canQueue'] )
			{
				$this->sphinxClient->SetFilter( 'soft_deleted', array( 0 ) );
				$this->sphinxClient->SetFilter( 'approved', array( 0,1 ) );
			}
			else
			{
				$this->sphinxClient->SetFilter( 'soft_deleted', array( 0 ) );
				$this->sphinxClient->SetFilter( 'approved', array( 1 ) );
			}
		}
		
		if ( IPSSearchRegistry::get('in.userMode') == 'title' )
		{
			$this->sphinxClient->SetGroupDistinct ( "tid" );
			$this->sphinxClient->SetGroupBy( 'tid', SPH_GROUPBY_ATTR, '@group DESC' );
			
			$this->sphinxClient->SetSortMode( SPH_SORT_ATTR_DESC, 'last_post' );
			
			$result = $this->sphinxClient->Query( '', 'forums_search_posts_main,forums_search_posts_delta' );
			
			$this->logSphinxWarnings();
		}
		else
		{
			if ( IPSSearchRegistry::get('in.userMode') == 'content' )
			{
				$this->sphinxClient->SetSortMode( SPH_SORT_ATTR_DESC, 'post_date' );
			}
			else
			{
				$this->sphinxClient->SetSortMode( SPH_SORT_ATTR_DESC, 'post_date' );
				$this->sphinxClient->SetGroupBy( 'last_post_group', SPH_GROUPBY_ATTR, '@group desc' );
			}
			
			$result = $this->sphinxClient->Query(  '', 'forums_search_posts_main,forums_search_posts_delta' );
			
			$this->logSphinxWarnings();
		}

		if ( is_array( $result['matches'] ) && count( $result['matches'] ) )
		{
			foreach( $result['matches'] as $res )
			{
				$search_ids[] = ( IPSSearchRegistry::get('opt.searchTitleOnly') ) ? $res['attrs']['tid'] : $res['attrs']['search_id'];
			}
		}
		
		return array( 'count' => intval( $result['total_found'] ) > 1000 ? 1000 : $result['total_found'], 'resultSet' => $search_ids );
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
		$tids       = $this->registry->getClass('classItemMarking')->fetchCookieData( 'forums', 'items' );
		$oldStamp   = $this->registry->getClass('classItemMarking')->fetchOldestUnreadTimestamp( array(), 'forums' );
		$check      = IPS_UNIX_TIME_NOW - ( 86400 * 90 );
		$forumIdsOk = array();
		
		/* Loop through the forums and build a list of forums we're allowed access to */
		$bvnp = explode( ',', $this->settings['vnp_block_forums'] );
		
		IPSSearchRegistry::set('in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set('in.search_sort_order', 'desc' );
		IPSSearchRegistry::set('opt.searchTitleOnly' , true );
		IPSSearchRegistry::set('display.onlyTitles'  , true );
		IPSSearchRegistry::set('opt.noPostPreview'   , true );
		
		/* Get list of good forum IDs */
		$_forumIdsOk	= $this->registry->class_forums->fetchSearchableForumIds( $this->memberData['member_id'], $bvnp );
		
		if ( ! $this->memberData['bw_vnc_type'] )
		{
			$oldStamp   = IPSLib::fetchHighestNumber( array( intval( $this->memberData['_cache']['gb_mark__forums'] ), intval( $this->memberData['last_visit'] ) ) );
			$tids       = array();
			
			$forumIdsOk = $_forumIdsOk;
		}
		else
		{
			foreach( $_forumIdsOk as $id )
			{
				$lMarked    = $this->registry->getClass('classItemMarking')->fetchTimeLastMarked( array( 'forumID' => $id ), 'forums' );
				$fData      = $this->registry->getClass('class_forums')->forumsFetchData( $id );
			
				if ( $fData['last_post'] > $lMarked )
				{
					/* Add to tids */
					$_tids = $this->registry->getClass('classItemMarking')->fetchReadIds( array( 'forumID' => $id ), 'forums', false );
					
					if ( is_array( $_tids ) )
					{
						foreach( $_tids as $k => $v )
						{
							$tids[ $k ] = $v;
						}
					}
					
					$forumIdsOk[ $id ] = $id;
				}
			}
			
			if ( intval( $this->memberData['_cache']['gb_mark__forums'] ) > 0  )
			{
				$oldStamp = $this->memberData['_cache']['gb_mark__forums'];
			}
			
			/* Finalize times */
			if ( ! $oldStamp OR $oldStamp == IPS_UNIX_TIME_NOW )
			{
				$oldStamp = intval( $this->memberData['last_visit'] );
			}
			
			/* Older than 3 months.. then limit */
			if ( $oldStamp < $check )
			{
				$oldStamp = $check;
			}
			
			/* If no forums, we're done */
			if ( ! count( $forumIdsOk ) )
			{
				/* Return it */
				return array( 'count' => 0, 'resultSet' => array() );
			}
		}
		
		/* Set the timestamps */
		$this->sphinxClient->SetFilterRange( 'last_post', $oldStamp, time() );
		$this->setDateRange( 0, 0 );
		
		/* Force it into filter so that search can pick it up */
		ipsRegistry::$request['search_app_filters']['forums']['forums'] = $forumIdsOk;
		
		/* Try and limit the TIDS */
		if ( is_array( $tids ) AND count( $tids ) )
		{
			if ( count( $tids ) > 300 )
			{
				/* Sort by last read date */
				arsort( $tids, SORT_NUMERIC );
				$tids = array_slice( $tids, 0, 300 );
			}
							
			$this->DB->build( array( 'select' => 'tid, last_post',
							   		 'from'   => 'topics',
							   	     'where'  => "tid IN (" . implode( ",", array_keys( $tids ) ) . ')' ) );
							   
			$this->DB->execute();
			
			while( $row = $this->DB->fetch() )
			{
				/* Posted in since last read? */
				if ( $row['last_post'] > $tids[ $row['tid'] ] )
				{
					unset( $tids[ $row['tid'] ] );
				}
			}
			
			if ( count( $tids ) )
			{
				$tids = array_keys( $tids );
			}
		}
		
		/* Set read tids */
		if ( count( $tids ) )
		{
			$this->sphinxClient->SetFilter( 'tid', $tids, TRUE );
		}
		
		/* Set up some vars */
		IPSSearchRegistry::set('set.resultCutToDate', $oldStamp );
		
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
		
		/* Loop through the forums and build a list of forums we're allowed access to */
		$bvnp = explode( ',', $this->settings['vnp_block_forums'] );
		
		IPSSearchRegistry::set('in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set('in.search_sort_order', 'desc' );
		IPSSearchRegistry::set('opt.searchTitleOnly' , true );
		IPSSearchRegistry::set('display.onlyTitles'  , true );
		IPSSearchRegistry::set('opt.noPostPreview'   , true );
		
		/* Get list of good forum IDs */
		$forumIdsOk	= $this->registry->class_forums->fetchSearchableForumIds( $this->memberData['member_id'], $bvnp );

		/* Force it into filter so that search can pick it up */
		ipsRegistry::$request['search_app_filters']['forums']['forums'] = $forumIdsOk;
		
		/* Set the timestamps */
		$this->sphinxClient->SetFilterRange( 'last_post', intval( time() - $seconds ), time() );
		$this->setDateRange( 0, 0 );
		
		return $this->search();
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
		$column = $column == 'member_id'     ? ( IPSSearchRegistry::get('opt.searchTitleOnly') ? 'starter_id' : 'author_id' ) : $column;
		$column = $column == 'content_title' ? 'title'     : $column;
		$column = $column == 'type_id'       ? 'forum_id'  : $column;
		
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
		IPSSearchRegistry::set( 'opt.noPostPreview'  , true );
		IPSSearchRegistry::set( 'opt.onlySearchPosts', false );
		
		if( isset( $data ) && is_array( $data ) && count( $data ) )
		{
			foreach( $data as $field => $_data )
			{
				/* CONTENT ONLY */
				if ( $field == 'noPreview' AND $_data['noPreview'] == 0 )
				{ 
					IPSSearchRegistry::set( 'opt.noPostPreview', false );
				}

				/* CONTENT ONLY */
				if ( $field == 'contentOnly' AND $_data['contentOnly'] == 1 )
				{
					IPSSearchRegistry::set( 'opt.onlySearchPosts', true );
				}

				/* POST COUNT */
				if ( $field == 'pCount' AND intval( $_data['pCount'] ) > 0 )
				{
					IPSSearchRegistry::set( 'opt.pCount', intval( $_data['pCount'] ) );
				}

				/* TOPIC VIEWS */
				if ( $field == 'pViews' AND intval( $_data ) > 0 )
				{
					IPSSearchRegistry::set( 'opt.pViews', intval( $_data['pViews'] ) );
				}
			}

			return $return;
		}
		else
		{
			return '';
		}
	}
}