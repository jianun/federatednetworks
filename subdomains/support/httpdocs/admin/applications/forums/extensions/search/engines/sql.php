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
		/* INIT */ 
		$count       		= 0;
		$results     		= array();
		$sort_by     		= IPSSearchRegistry::get('in.search_sort_by');
		$sort_order         = IPSSearchRegistry::get('in.search_sort_order');
		$search_term        = IPSSearchRegistry::get('in.clean_search_term');
		$content_title_only = IPSSearchRegistry::get('opt.searchTitleOnly');
		$post_search_only   = IPSSearchRegistry::get('opt.onlySearchPosts');
		$order_dir 			= ( $sort_order == 'asc' ) ? 'asc' : 'desc';
		$rows    			= array();
		$count   			= 0;
		$c                  = 0;
		$got     			= 0;
		$sortKey			= '';
		$sortType			= '';
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
		
		/* Sorting */
		switch( $sort_by )
		{
			default:
			case 'date':
				$sortKey  = ( $content_title_only ) ? 'last_post' : 'post_date';
				$sortType = 'numerical';
			break;
			case 'title':
				$sortKey  = 'title';
				$sortType = 'string';
			break;
			case 'posts':
				$sortKey  = 'posts';
				$sortType = 'numerical';
			break;
			case 'views':
				$sortKey  = 'views';
				$sortType = 'numerical';
			break;
		}
				
		/* Search in titles */
		if ( $content_title_only )
		{
			/* Do the search */
			$this->DB->build( array( 
									'select'   => "t.tid as id, t.tid as topic_id, t.title, t.posts, t.views, t.last_post",
									'from'	   => 'topics t',
	 								'where'	   => $this->_buildWhereStatement( $search_term, $content_title_only, $order ),
									'limit'    => array(0, IPSSearchRegistry::get('set.hardLimit') + 1),
									'order'    => 't.'.$sortKey . ' ' . $order_dir ) );
		}
		else
		{
			/* Do the search */
			$this->DB->build( array( 
									'select'   => "p.pid as id, post_date, p.topic_id",
									'from'	   => array( 'posts' => 'p' ),
	 								'where'	   => $this->_buildWhereStatement( $search_term, $content_title_only, $order, null, ( IPSSearchRegistry::get('opt.onlySearchPosts') || IPSSearchRegistry::get('in.search_author') ) ? false : true ),
									'limit'    => array(0, IPSSearchRegistry::get('set.hardLimit') + 1),
									'order'    => $sortKey . ' ' . $sort_order,
									'add_join' => array( array( 'select' => 't.title, t.posts, t.views',
																'from'	 => array( 'topics' => 't' ),
												 				'where'	 => 't.tid=p.topic_id',
												 				'type'	 => 'left' ) ) ) );
		}

		$DB = $this->DB->execute();
		
		/* Fetch count 
		$count = intval( $this->DB->getTotalRows( $DB ) );
		
		if ( $count > IPSSearchRegistry::get('set.hardLimit') )
		{
			$count = IPSSearchRegistry::get('set.hardLimit');
			
			IPSSearchRegistry::set('set.resultsCutToLimit', true );
		}*/
		
		/* Fetch all to sort */
		while( $r = $this->DB->fetch( $DB ) )
		{
			if ( IPSSearchRegistry::get('opt.noPostPreview') )
			{
				$_rows[ $r['topic_id'] ] = $r;
			}
			else
			{
				$_rows[ $r['id'] ] = $r;
			}
		}
		
		/* Fetch count */
		$count = count( $_rows );	
		
		if ( $count > IPSSearchRegistry::get('set.hardLimit') )
		{
			$count = IPSSearchRegistry::get('set.hardLimit');
			
			IPSSearchRegistry::set('set.resultsCutToLimit', true );
		}

		/* Set vars */
		IPSSearch::$ask = $sortKey;
		IPSSearch::$aso = strtolower( $order_dir );
		IPSSearch::$ast = $sortType;
		
		/* Sort */
		if ( count( $_rows ) )
		{
			usort( $_rows, array("IPSSearch", "usort") );
		
			/* Build result array */
			foreach( $_rows as $r )
			{
				$c++;
				
				if ( IPSSearchRegistry::get('in.start') AND IPSSearchRegistry::get('in.start') >= $c )
				{
					continue;
				}
				
				$rows[ $got ] = $r['id'];
							
				$got++;
				
				/* Done? */
				if ( IPSSearchRegistry::get('opt.search_per_page') AND $got >= IPSSearchRegistry::get('opt.search_per_page') )
				{
					break;
				}
			}
		}
	
		/* Return it */
		return array( 'count' => $count, 'resultSet' => $rows );
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
		
		switch( IPSSearchRegistry::get('in.userMode') )
		{
			default:
			case 'all': 
				IPSSearchRegistry::set('opt.searchTitleOnly', true );
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
		
		/* Ensure we limit by date */
		$this->settings['search_ucontent_days'] = ( $this->settings['search_ucontent_days'] ) ? $this->settings['search_ucontent_days'] : 365;
		
		/* Get list of good forum IDs */
		$forumIdsOk	= $this->registry->class_forums->fetchSearchableForumIds( $this->memberData['member_id'] );

		$forumIdsOk  	= ( count( $forumIdsOk ) ) ? $forumIdsOk : array( 0 => 0 );
		$topic_where[]	= "t.forum_id IN (" . implode( ",", $forumIdsOk ) . ")";
		
		if ( IPSSearchRegistry::get('in.userMode') != 'title' )
		{
			$where[] = "p.author_id=" . intval( $member['member_id'] );
		}
		
		if ( $this->settings['search_ucontent_days'] )
		{
			if ( IPSSearchRegistry::get('in.userMode') != 'title' )
			{
				$where[]       = "p.post_date > " . ( time() - ( 86400 * intval( $this->settings['search_ucontent_days'] ) ) );
			}
			
			$topic_where[] = "t.start_date > " . ( time() - ( 86400 * intval( $this->settings['search_ucontent_days'] ) ) );
		}
				
		/* Set up perms */
		$permissions['TopicSoftDeleteSee']  = $this->registry->getClass('class_forums')->canSeeSoftDeletedTopics( 0 );
		$permissions['canQueue']			= $this->registry->getClass('class_forums')->canQueuePosts( 0 );
		$permissions['PostSoftDeleteSee']   = $this->registry->getClass('class_forums')->canSeeSoftDeletedPosts( 0 );
		$permissions['SoftDeleteReason']       = $this->registry->getClass('class_forums')->canSeeSoftDeleteReason( 0 );
		$permissions['SoftDeleteContent']      = $this->registry->getClass('class_forums')->canSeeSoftDeleteContent( 0 );
		
		/* Exclude some items */
		if ( IPSSearchRegistry::get('in.userMode') != 'title' )
		{
			if ( $permissions['SoftDeleteContent'] AND $permissions['PostSoftDeleteSee'] AND $permissions['canQueue'] )
			{
				$where[] = $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'hidden', 'sdeleted'), 'p.');
			}
			else if ( $permissions['SoftDeleteContent'] AND $permissions['PostSoftDeleteSee'] )
			{
				$where[] = $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'sdeleted'), 'p.');
			}
			else if ( $permissions['canQueue'] )
			{
				$where[] = $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'hidden'), 'p.');
			}
			else
			{
				$where[] = $this->registry->class_forums->fetchPostHiddenQuery(array('visible' ), 'p.' );
			}
		}
		
		if ( $permissions['TopicSoftDeleteSee'] AND $permissions['canQueue'] )
		{
			$topic_where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'hidden', 'sdeleted'), 't.' );
		}
		else if ( $permissions['TopicSoftDeleteSee'] )
		{
			$topic_where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'sdeleted'), 't.' );
		}
		else if ( $permissions['canQueue'] )
		{
			$topic_where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'hidden'), 't.' );
		}
		else
		{
			$topic_where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible' ), 't.' );
		}

		/* Manual fetch if user content all */
		if ( IPSSearchRegistry::get('in.userMode') == 'all' )
		{
			/* init */
			$pids = array();
			
			$this->DB->build( array( 'select'   => 't.tid, t.last_post, t.topic_firstpost',
									 'from'     => 'topics t',
									 'where'    => implode( ' AND ', $topic_where ) . " AND t.starter_id=" . intval( $member['member_id'] ),
									 'order'    => 't.last_post DESC',
									 'limit'    => array(0, 1000 ) ) );
			$this->DB->execute();
			
			while( $t = $this->DB->fetch() )
			{
				$pids[ $t['tid'] ] = $t['last_post'];
			}
			
			$where = ( array_merge( (array) $where, (array) $topic_where ) );
			$where = implode( " AND ", $where );
			
			/* Now for posts */
			$this->DB->build( array( 'select'   => 'p.pid',
									 'from'     => array('posts' => 'p' ),
									 'where'    => $where,
									 'add_join' => array( array( 'select' => 't.tid, t.last_post, t.topic_firstpost',
									 							 'from'   => array( 'topics' => 't' ),
							 		  							 'where'  => 'p.topic_id=t.tid',
							 		  							 'type'   => 'left' ) ),
									 'order'    => 'p.pid DESC',
									 'limit'    => array( 0, 1000 ) ) );
									 
			$this->DB->execute();
			
			while( $t = $this->DB->fetch() )
			{
				$pids[ $t['tid'] ] = $t['last_post'];
			}
			
			$count = array( 'count' => count( $pids ) );
			
			if ( $count['count'] )
			{
				arsort( $pids, SORT_NUMERIC );
				$tids = array_slice( array_keys( $pids ), $start, $perPage );
			} 
		}
		else if ( IPSSearchRegistry::get('in.userMode') == 'title' )
		{
			$topic_where[] = "t.starter_id=" . intval( $member['member_id'] );
			
			$where = ( array_merge( (array) $where, (array) $topic_where ) );
			$where = implode( " AND ", $where );

			/* Fetch the count */
			$count = $this->DB->buildAndFetch( array( 'select'   => 'COUNT(tid) as count',
											  		  'from'     => 'topics t',
											 		  'where'    => $where ) );
									 
			/* Fetch the count */
			if ( $count['count'] )
			{
				$this->DB->build( array( 'select'   => 'tid',
										 'from'     => 'topics t',
										 'where'    => $where,
										 'order'    => 't.tid DESC',
										 'limit'    => array( $start, $perPage ) ) );
										
				$this->DB->execute();
			
				while( $row = $this->DB->fetch( $o ) )
				{
					$tids[ $row['tid'] ] = $row['tid'];
				}
			}
		}
		else
		{
			$where = ( array_merge( (array) $where, (array) $topic_where ) );
			$where = implode( " AND ", $where );
			
			/* Fetch the count */
			$count = $this->DB->buildAndFetch( array( 'select'   => 'COUNT(tid) as count',
											  		  'from'     => array('topics' => 't' ),
											 		  'where'    => $where,
											 		  'add_join' => array( array( 'from'   => array( 'posts' => 'p' ),
											 		  							  'where'  => 'p.topic_id=t.tid',
											 		  							  'type'   => 'left' ) ) ) );
									 
			/* Fetch the count */
			if ( $count['count'] )
			{
				$this->DB->build( array( 'select'   => 'tid',
										 'from'     => array('topics' => 't' ),
										 'where'    => $where,
										 'add_join' => array( array( 'select' => 'p.pid',
								 		  							 'from'   => array( 'posts' => 'p' ),
								 		  							 'where'  => 'p.topic_id=t.tid',
								 		  							 'type'   => 'left' ) ),
										 'order'    => 't.last_post DESC',
										 'limit'    => array( $start, $perPage ) ) );
										
				$this->DB->execute();
			
				while( $row = $this->DB->fetch( $o ) )
				{
					$tids[ $row['pid'] ] = $row['pid'];
				}
			}
		}
		
		/* Fix to 1000 results max */
		$count['count'] = ( $count['count'] > 1000 ) ? 1000 : $count['count'];
	
		/* Return it */
		return array( 'count' => $count['count'], 'resultSet' => $tids );
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
		$rtids      = array();
		$tids       = $this->registry->getClass('classItemMarking')->fetchCookieData( 'forums', 'items' );
		$oldStamp   = $this->registry->getClass('classItemMarking')->fetchOldestUnreadTimestamp( array(), 'forums' );
		$check      = IPS_UNIX_TIME_NOW - ( 86400 * 90 );
		$forumIdsOk = array();
		
		/* Loop through the forums and build a list of forums we're allowed access to */
		$bvnp       = explode( ',', $this->settings['vnp_block_forums'] );
		$start		= IPSSearchRegistry::get('in.start');
		$perPage    = IPSSearchRegistry::get('opt.search_per_page');
		
		IPSSearchRegistry::set('in.search_sort_by'   , 'date' );
		IPSSearchRegistry::set('in.search_sort_order', 'desc' );
		IPSSearchRegistry::set('opt.searchTitleOnly' , true );
		IPSSearchRegistry::set('display.onlyTitles'  , true );
		IPSSearchRegistry::set('opt.noPostPreview'   , true );
		
		if ( intval( $this->memberData['_cache']['gb_mark__forums'] ) > 0 )
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
	
		/* Get list of good forum IDs */
		$_forumIdsOk	= $this->registry->class_forums->fetchSearchableForumIds( $this->memberData['member_id'], $bvnp );
				
		if ( ! $this->memberData['bw_vnc_type'] )
		{
			$oldStamp = IPSLib::fetchHighestNumber( array( intval( $this->memberData['_cache']['gb_mark__forums'] ), intval( $this->memberData['last_visit'] ) ) );
			$where[]  = "last_post > " .  $oldStamp;
			
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
			
			/* If no forums, we're done */
			if ( ! count( $forumIdsOk ) )
			{
				/* Return it */
				return array( 'count' => 0, 'resultSet' => array() );
			}
			
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
			
			/* Based on oldest timestamp */
			$where[] = "last_post > " . $oldStamp;
			
			/* Set read tids */
			if ( count( $tids ) )
			{
				$where[] = "tid NOT IN (" . implode( ",", $tids ) . ')';
			}
		}
		
		$forumIdsOk	= ( count( $forumIdsOk ) ) ? $forumIdsOk : array( 0 => 0 );
		$where[]	= "forum_id IN (" . implode( ",", $forumIdsOk ) . ")";
		
		/* Add in last bits */
		$where[] = "state != 'link'";
		
		/* Set up perms */
		$permissions['TopicSoftDeleteSee']  = $this->registry->getClass('class_forums')->canSeeSoftDeletedTopics( 0 );
		$permissions['canQueue']			= $this->registry->getClass('class_forums')->canQueuePosts( 0 );
		
		if ( $permissions['TopicSoftDeleteSee'] AND $permissions['canQueue'] )
		{
			$where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'hidden', 'sdeleted') );
		}
		else if ( $permissions['TopicSoftDeleteSee'] )
		{
			$where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'sdeleted') );
		}
		else if ( $permissions['canQueue'] )
		{
			$where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'hidden') );
		}
		else
		{
			$where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible' )  );
		}

		$where = implode( " AND ", $where );
		
		/* Fetch the count */
		$count = $this->DB->buildAndFetch( array( 'select'   => 'count(*) as count',
										  		  'from'     => 'topics',
										 		  'where'    => $where ) );
								 
		/* Fetch the count */
		if ( $count['count'] )
		{
			$this->DB->build( array( 'select'   => 'tid',
									 'from'     => 'topics',
									 'where'    => $where,
									 'order'    => 'last_post DESC',
									 'limit'    => array( $start, $perPage ) ) );
									
			$this->DB->execute();
			
			
		
			while( $row = $this->DB->fetch( $o ) )
			{
				$rtids[ $row['tid'] ] = $row['tid'];
			}
		}
		
		/* Set up some vars */
		IPSSearchRegistry::set('set.resultCutToDate', $oldStamp );
		
		/* Return it */
		return array( 'count' => $count['count'], 'resultSet' => $rtids );
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
		$forumIdsOk	= array();
		$bvnp		= explode( ',', $this->settings['vnp_block_forums'] );
		$start		= IPSSearchRegistry::get('in.start');
		$perPage    = IPSSearchRegistry::get('opt.search_per_page');
		$tids	    = array();
		
		/* Get list of good forum IDs */
		$forumIdsOk	= $this->registry->class_forums->fetchSearchableForumIds( $this->memberData['member_id'], $bvnp );

		$forumIdsOk	= ( count( $forumIdsOk ) ) ? $forumIdsOk : array( 0 => 0 );
		$where[]	= "t.forum_id IN (" . implode( ",", $forumIdsOk ) . ")";

		/* Generate last post times */
		$where[] = "t.last_post > " . intval( time() - $seconds );
		
		/* Set up perms */
		$permissions['TopicSoftDeleteSee']  = $this->registry->getClass('class_forums')->canSeeSoftDeletedTopics( 0 );
		$permissions['canQueue']			= $this->registry->getClass('class_forums')->canQueuePosts( 0 );
		
		/* Add in last bits */
		$where[] = "t.state != 'link'";
		
		if ( $permissions['TopicSoftDeleteSee'] AND $permissions['canQueue'] )
		{
			$where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'hidden', 'sdeleted'), 't.');
		}
		else if ( $permissions['TopicSoftDeleteSee'] )
		{
			$where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'sdeleted'), 't.');
		}
		else if ( $permissions['canQueue'] )
		{
			$where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'hidden'), 't.');
		}
		else
		{
			$where[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible' ), 't.' );
		}
		
		$where = implode( " AND ", $where );
		
		/* Fetch the count */
		$count = $this->DB->buildAndFetch( array( 'select'   => 'COUNT(tid) as count',
												  'from'     => array( 'topics' => 't' ),
												  'where'    => $where ) );
								
		
		/* Grab */
		$this->DB->build( array( 'select'   => 'tid',
								 'from'     => array( 'topics' => 't' ),
								 'where'    => $where,
								 'order'    => 'last_post DESC',
								 'limit'    => array( $start, $perPage ) ) );
								
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$tids[ $row['tid'] ] = $row['tid'];
		}
		
		/* Return it */
		return array( 'count' => $count['count'], 'resultSet' => $tids );
	}

	/**
	 * Builds the where portion of a search string
	 *
	 * @access	private
	 * @param	string	$search_term		The string to use in the search
	 * @param	bool	$content_title_only	Search only title records
	 * @param	string	$order				Order by data
	 * @param	bool	$onlyPosts			Enforce posts only
	 * @param	bool	$noForums			Don't check forums that posts are in
	 * @return	string
	 **/
	private function _buildWhereStatement( $search_term, $content_title_only=false, $order='', $onlyPosts=null, $noForums=false )
	{		
		/* INI */
		$where_clause = array();
		$onlyPosts    = ( $onlyPosts !== null ) ? $onlyPosts : IPSSearchRegistry::get('opt.onlySearchPosts');
		$sort_by      = IPSSearchRegistry::get('in.search_sort_by');
		$sort_order   = IPSSearchRegistry::get('in.search_sort_order');
		$sortKey	  = '';
		$sortType	  = '';
		$cType        = IPSSearchRegistry::get('contextual.type');
		$cId		  = IPSSearchRegistry::get('contextual.id' );
		
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

					if ( ( ! $data['sub_can_post'] OR ! $data['can_view_others'] ) AND !$this->memberData['g_access_cp'] )
					{
						$forumIdsBad[] = $forum_id;
						continue;
					}
				
					$forumIdsOk[] = $forum_id;
				}
			}
		}
		
		if( !count($forumIdsOk) )
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
				$where_clause[] = "t.forum_id=" . $cId;
			}
			else
			{
				$where_clause[] = "t.forum_id IN (" . implode( ",", $forumIdsOk ) . ")";
			}
		}
		
		/* Topic contextual */
		if ( $cType == 'topic' AND $cId )
		{
			$where_clause[] = "t.tid=" . $cId;
		}
			
		/* Exclude some items */
		$permissions['TopicSoftDeleteSee']  = $this->registry->getClass('class_forums')->canSeeSoftDeletedTopics( 0 );
		$permissions['canQueue']			= $this->registry->getClass('class_forums')->canQueuePosts( 0 );
		$permissions['PostSoftDeleteSee']   = $this->registry->getClass('class_forums')->canSeeSoftDeletedPosts( 0 );
		$permissions['SoftDeleteReason']       = $this->registry->getClass('class_forums')->canSeeSoftDeleteReason( 0 );
		$permissions['SoftDeleteContent']      = $this->registry->getClass('class_forums')->canSeeSoftDeleteContent( 0 );

		if ( ! $content_title_only )
		{
			if ( $permissions['SoftDeleteContent'] AND $permissions['PostSoftDeleteSee'] AND $permissions['canQueue'] )
			{
				$where_clause[] = $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'hidden', 'sdeleted'), 'p.');
			}
			else if ( $permissions['SoftDeleteContent'] AND $permissions['PostSoftDeleteSee'] )
			{
				$where_clause[] = $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'sdeleted'), 'p.');
			}
			else if ( $permissions['canQueue'] )
			{
				$where_clause[] = $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'hidden'), 'p.');
			}
			else
			{
				$where_clause[] = $this->registry->class_forums->fetchPostHiddenQuery(array('visible' ), 'p.' );
			}
		}

		if ( $permissions['SoftDeleteContent'] AND $permissions['TopicSoftDeleteSee'] AND $permissions['canQueue'] )
		{
			$where_clause[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'hidden', 'sdeleted'), 't.');
		}
		else if ( $permissions['SoftDeleteContent'] AND $permissions['TopicSoftDeleteSee'] )
		{
			$where_clause[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'sdeleted'), 't.');
		}
		else if ( $permissions['canQueue'] )
		{
			$where_clause[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible', 'hidden'), 't.');
		}
		else
		{
			$where_clause[] = $this->registry->class_forums->fetchTopicHiddenQuery(array('visible' ), 't.' );
		}

		if( $search_term )
		{
			$search_term = str_replace( '&quot;', '"', $search_term );
			
			if( $content_title_only )
			{			
				$where_clause[] = $this->DB->buildSearchStatement( 't.title', $search_term, true, false, ipsRegistry::$settings['use_fulltext'] );
			}
			else
			{
				if ( $onlyPosts )
				{
					$where_clause[] = $this->DB->buildSearchStatement( 'p.post', $search_term, true, false, ipsRegistry::$settings['use_fulltext'] );
				}
				else
				{
					/* Sorting */
					switch( $sort_by )
					{
						default:
						case 'date':
							$sortKey  = 'last_post';
							$sortType = 'numerical';
						break;
						case 'title':
							$sortKey  = 'title';
							$sortType = 'string';
						break;
						case 'posts':
							$sortKey  = 'posts';
							$sortType = 'numerical';
						break;
						case 'views':
							$sortKey  = 'views';
							$sortType = 'numerical';
						break;
					}

					/* Set vars */
					IPSSearch::$ask = $sortKey;
					IPSSearch::$aso = strtolower( $sort_order );
					IPSSearch::$ast = $sortType;
			
					/* Find topic ids that match */
					$tids = array( 0 => 0 );
					$pids = array( 0 => 0 );
					
					$this->DB->build( array( 
											'select'   => "t.tid, t.last_post, t.forum_id",
											'from'	   => 'topics t',
			 								'where'	   => str_replace( 'p.author_id', 't.starter_id', $this->_buildWhereStatement( $search_term, true, $order, null ) ),
			 								'order'    => 't.' . $sortKey . ' ' . $sort_order,
											'limit'    => array(0, IPSSearchRegistry::get('set.hardLimit')) ) );
								
					$i = $this->DB->execute();
					
					/* Grab the results */
					while( $row = $this->DB->fetch( $i ) )
					{
						$_rows[ $row['tid'] ] = $row;
					}
			
					/* Sort */
					if ( count( $_rows ) )
					{
						usort( $_rows, array("IPSSearch", "usort") );
				
						foreach( $_rows as $id => $row )
						{
							$tids[] = $row['tid'];
						}
					}
					
					/* Now get the Pids */
					if ( count( $tids ) > 1 )
					{
						$this->DB->build( array('select'  => 'pid',
												'from'	  => 'posts',
												'where'   => 'topic_id IN ('. implode( ',', $tids ) . ') AND new_topic=1' ) );
						
						$i = $this->DB->execute();
						
						while( $row = $this->DB->fetch() )
						{
							$pids[ $row['pid'] ] = $row['pid'];
						}
					}
					
					/* Set vars */
					IPSSearch::$ask = ( $sortKey == 'last_post' ) ? 'post_date' : $sortKey;
					IPSSearch::$aso = strtolower( $sort_order );
					IPSSearch::$ast = $sortType;
					
					$this->DB->build( array( 
											'select'   => "p.pid, p.queued",
											'from'	   => array( 'posts' => 'p' ),
			 								'where'	   => $this->_buildWhereStatement( $search_term, false, $order, true ),
			 								'order'    => IPSSearch::$ask . ' ' . IPSSearch::$aso,
											'limit'    => array(0, IPSSearchRegistry::get('set.hardLimit')),
											'add_join' => array( array( 'select' => 't.approved, t.forum_id',
																		'from'   => array( 'topics' => 't' ),
																		'where'  => 'p.topic_id=t.tid',
																		'type'   => 'left' ) ) ) );
								
					$i = $this->DB->execute();
					
					/* Grab the results */
					while( $row = $this->DB->fetch( $i ) )
					{
						$_prows[ $row['pid'] ] = $row;
					}
			
					/* Sort */
					if ( count( $_prows ) )
					{
						usort( $_prows, array("IPSSearch", "usort") );
						
						foreach( $_prows as $id => $row )
						{
							$pids[ $row['pid'] ] = $row['pid'];
						}
					}
					
					$where_clause[] = '( p.pid IN (' . implode( ',', $pids ) .') )';
				}
			}
		}
	
		/* No moved topic links */
		$where_clause[] = "t.state != 'link'";

		/* Date Restrict */
		if( $this->search_begin_timestamp && $this->search_end_timestamp )
		{ 
			$where_clause[] = $this->DB->buildBetween( $content_title_only ? "t.last_post" : "p.post_date", $this->search_begin_timestamp, $this->search_end_timestamp );
		}
		else
		{
			if( $this->search_begin_timestamp )
			{
				$where_clause[] = $content_title_only ? "t.last_post > {$this->search_begin_timestamp}" : "p.post_date > {$this->search_begin_timestamp}";
			}
			
			if( $this->search_end_timestamp )
			{
				$where_clause[] = $content_title_only ? "t.last_post < {$this->search_end_timestamp}" : "p.post_date < {$this->search_end_timestamp}";
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
		$column = $column == 'member_id'     ? ( IPSSearchRegistry::get('opt.searchTitleOnly') ? 't.starter_id' : 'p.author_id' ) : $column;
		$column = $column == 'content_title' ? 't.title'     : $column;
		$column = $column == 'type_id'       ? 't.forum_id'  : $column;
		
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
		
		/* Make default search type topics */
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
					$return[] = array( 'column' => 't.posts', 'operator' => '>=', 'value' => intval( $_data['pCount'] ) );
				}

				/* TOPIC VIEWS */
				if ( $field == 'pViews' AND intval( $_data ) > 0 )
				{
					$return[] = array( 'column' => 't.views', 'operator' => '>=', 'value' => intval( $_data['pViews'] ) );
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