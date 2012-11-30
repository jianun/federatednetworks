<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Formats forum search results
 * Last Updated: $Date: 2010-02-19 01:29:54 +0000 (Fri, 19 Feb 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5855 $
 **/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class search_format_forums extends search_format
{
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry )
	{
		/* Get class forums, used for displaying forum names on results */
		if ( ipsRegistry::isClassLoaded('class_forums') !== TRUE )
		{
			require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php" );
			ipsRegistry::setClass( 'class_forums', new class_forums( ipsRegistry::instance() ) );
			ipsRegistry::getClass( 'class_forums' )->forumsInit();
		}
		
		parent::__construct( $registry );
		
		/* Set up wrapper */
		$this->templates = array( 'group' => 'search', 'template' => 'searchResultsAsForum' );
	}
	
	/**
	 * Parse search results
	 *
	 * @access	private
	 * @param	array 	$r				Search result
	 * @return	array 	$html			Blocks of HTML
	 */
	public function parseAndFetchHtmlBlocks( $rows )
	{
		/* Forum stuff */
		$sub               = false;
		$isVnc             = false;
		$search_term	   = IPSSearchRegistry::get('in.clean_search_term');
		$onlyPosts		   = IPSSearchRegistry::get('opt.onlySearchPosts');
		$onlyTitles		   = IPSSearchRegistry::get('display.onlyTitles');
		$noPostPreview	   = IPSSearchRegistry::get('opt.noPostPreview');
		$results		   = array();
		$attachPids		   = array();
		$results		   = array();
		
		/* loop and process */
		foreach( $rows as $id => $data )
		{	
			/* Reset */
			$pages = 0;
			
			/* Set up forum */
			$forum = $this->registry->getClass('class_forums')->forum_by_id[ $data['forum_id'] ];

			$this->last_topic = $data['tid'];
	
			/* Various data */
			$data['_last_post']  = $data['last_post'];
			$data['_longTitle']  = $data['content_title'];
			$data['_shortTitle'] = IPSText::truncate( $data['content_title'], 60 );
			$data['last_poster'] = $data['last_poster_id'] ? IPSLib::makeProfileLink( $data['last_poster_name'], $data['last_poster_id'], $data['seo_last_name'] ) : $this->settings['guest_name_pre'] . $data['last_poster_name'] . $this->settings['guest_name_suf'];
			$data['starter']     = $data['starter_id']     ? IPSLib::makeProfileLink( $data['starter_name'], $data['starter_id'], $data['seo_first_name'] ) : $this->settings['guest_name_pre'] . $data['starter_name'] . $this->settings['guest_name_suf'];
			$data['last_post']   = $this->registry->getClass( 'class_localization')->getDate( $data['last_post'], 'SHORT' );
	
			if ( isset( $data['post_date'] ) )
			{
				$data['_post_date']	= $data['post_date'];
				$data['post_date']	= $this->registry->getClass( 'class_localization')->getDate( $data['post_date'], 'SHORT' );
			}
	
			if ( $this->memberData['is_mod'] )
			{
				$data['posts'] += intval($data['topic_queuedposts']);
			}
	
			if ( $data['posts'])
			{
				if ( (($data['posts'] + 1) % $this->settings['display_max_posts']) == 0 )
				{
					$pages = ($data['posts'] + 1) / $this->settings['display_max_posts'];
				}
				else
				{
					$number = ( ($data['posts'] + 1) / $this->settings['display_max_posts'] );
					$pages = ceil( $number);
				}
			}
	
			if ( $pages > 1 )
			{
				for ( $i = 0 ; $i < $pages ; ++$i )
				{
					$real_no = $i * $this->settings['display_max_posts'];
					$page_no = $i + 1;
	
					if ( $page_no == 4 and $pages > 4 )
					{
						$data['pages'][] = array(  'last'   => 1,
						 					       'st'     => ($pages - 1) * $this->settings['display_max_posts'],
						  						   'page'   => $pages );
						break;
					}
					else
					{
						$data['pages'][] = array( 'last' => 0,
												  'st'   => $real_no,
												  'page' => $page_no );
					}
				}
			}
		
			/* For-matt some stuffs */
			if ( ! $data['cache_content'] )
			{
				IPSText::getTextClass( 'bbcode' )->parse_smilies			= $data['use_emo'];
				IPSText::getTextClass( 'bbcode' )->parse_html				= ( $forum['use_html'] and $this->caches['group_cache'][ $data['member_group_id'] ]['g_dohtml'] and $data['post_htmlstate'] ) ? 1 : 0;
				IPSText::getTextClass( 'bbcode' )->parse_nl2br				= $data['post_htmlstate'] == 2 ? 1 : 0;
				IPSText::getTextClass( 'bbcode' )->parse_bbcode				= $forum['use_ibc'];
				IPSText::getTextClass( 'bbcode' )->parsing_section			= 'topics';
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $data['member_group_id'];
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $data['mgroup_others'];
			}
			else
			{
				$data['post'] = '<!--cached-' . gmdate( 'r', $data['cache_updated'] ) . '-->' . $data['cache_content'];
			}
			
			$data['post'] = IPSText::searchHighlight( IPSText::getTextClass( 'bbcode' )->preDisplayParse( $data['post'] ), $search_term );
			
			/* Has attachments */
			if ( $data['topic_hasattach'] )
			{
				$attachPids[ $data['pid'] ] = $data['post'];
			}
			
			$rows[ $id ] = $data;
		}
		
		/* Attachments */
		if ( count( $attachPids ) )
		{
			/* Load attachments class */
			if ( ! is_object( $this->class_attach ) )
			{
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'core' ) . '/sources/classes/attach/class_attach.php', 'class_attach' );
				$this->class_attach		   =  new $classToLoad( $this->registry );
				$this->class_attach->type  = 'post';
				$this->class_attach->init();
			}
			
			$attachHTML = $this->class_attach->renderAttachments( array( $data['pid'] => $data['post'] ), array_keys( $attachPids ) );
	
			/* Now parse back in the rendered posts */
			if( is_array($attachHTML) AND count($attachHTML) )
			{
				foreach( $attachHTML as $id => $_data )
				{
					/* Get rid of any lingering attachment tags */
					if ( stristr( $_data['html'], "[attachment=" ) )
					{
						$_data['html'] = IPSText::stripAttachTag( $_data['html'] );
					}
					
					$rows[ $id ]['post']           = $_data['html'];
					$rows[ $id ]['attachmentHtml'] = $_data['attachmentHtml'];
				}
			}
		}

		/* Go through and build HTML */
		foreach( $rows as $id => $data )
		{
			/* Format content */
			list( $html, $sub ) = $this->formatContent( $data );
			
			$results[ $id ] = array( 'html' => $html, 'app' => $data['app'], 'type' => $data['type'], 'sub' => $sub );
		}
		
		return $results;
	}
	
	/**
	 * Formats the forum search result for display
	 *
	 * @access	public
	 * @param	array   $search_row		Array of data from search_index
	 * @return	mixed	Formatted content, ready for display, or array containing a $sub section flag, and content
	 **/
	public function formatContent( $data )
	{
		$onlyPosts		   = IPSSearchRegistry::get('opt.onlySearchPosts');
		$onlyTitles		   = IPSSearchRegistry::get('display.onlyTitles');
		$noPostPreview	   = IPSSearchRegistry::get('opt.noPostPreview');

		/* Forum Breadcrum */
		$data['_forum_trail'] = $this->registry->getClass( 'class_forums' )->forumsBreadcrumbNav( $data['forum_id'] );

		/* Is it read?  We don't support last_vote in search. */
		$is_read	= $this->registry->getClass( 'classItemMarking' )->isRead( array( 'forumID' => $data['forum_id'], 'itemID' => $data['tid'], 'itemLastUpdate' => $data['lastupdate'] ? $data['lastupdate'] : $data['updated'] ), 'forums' );

		/* Has posted dot */
		$show_dots = ( $this->settings['show_user_posted'] AND $this->memberData['member_id'] AND  $data['_hasPosted'] ) ? 1 : 0;

		/* Icon */
		$data['_icon']   = $this->registry->getClass( 'class_forums' )->fetchTopicFolderIcon( $data, $show_dots, $is_read );
		$data['_isRead'] = $is_read;
		
		/* Display type */
		return array( $this->registry->getClass( 'output' )->getTemplate( 'search' )->topicPostSearchResultAsForum( $data, ( $onlyTitles || $noPostPreview ) ? 1 : 0 ), 0 );
	}

	/**
	 * Formats / grabs extra data for results
	 * Takes an array of IDS (can be IDs from anything) and returns an array of expanded data.
	 *
	 * @access public
	 * @return array
	 */
	public function processResults( $ids )
	{
		/* INIT */
		$sort_by     		= IPSSearchRegistry::get('in.search_sort_by');
		$sort_order         = IPSSearchRegistry::get('in.search_sort_order');
		$search_term        = IPSSearchRegistry::get('in.clean_search_term');
		$content_title_only = IPSSearchRegistry::get('opt.searchTitleOnly');
		$onlyPosts          = IPSSearchRegistry::get('opt.onlySearchPosts');
		$order_dir 			= ( $sort_order == 'asc' ) ? 'asc' : 'desc';
		$_post_joins		= array();
		$members			= array();
		$results			= array();
		$topicIds			= array();
		$dots				= array();
		$sortKey			= '';
		$sortType			= '';
		$_sdTids			= array();
		$_sdPids			= array();
		
		/* Set up some basic permissions */
		$permissions['PostSoftDeleteSee']      = $this->registry->getClass('class_forums')->canSeeSoftDeletedPosts( 0 );
		$permissions['TopicSoftDeleteSee']     = $this->registry->getClass('class_forums')->canSeeSoftDeletedTopics( 0 );
		$permissions['canQueue']			   = $this->registry->getClass('class_forums')->canQueuePosts( 0 );
		$permissions['SoftDeleteReason']       = $this->registry->getClass('class_forums')->canSeeSoftDeleteReason( 0 );
		$permissions['SoftDeleteContent']      = $this->registry->getClass('class_forums')->canSeeSoftDeleteContent( 0 );
		$permissions['PostSoftDeleteRestore']  = $this->registry->getClass('class_forums')->can_Un_SoftDeletePosts( 0 );
		$permissions['TopicSoftDeleteRestore'] = $this->registry->getClass('class_forums')->can_Un_SoftDeleteTopics( 0 );
		
		/* Got some? */
		if ( count( $ids ) )
		{
			/* Cache? */
			if ( IPSContentCache::isEnabled() )
			{
				if ( IPSContentCache::fetchSettingValue('post') )
				{
					$_post_joins[] = IPSContentCache::join( 'post', 'p.pid' );
				}
				
				if ( IPSContentCache::fetchSettingValue('sig') )
				{
					$_post_joins[] = IPSContentCache::join( 'sig' , 'p.author_id', 'ccb', 'left', 'ccb.cache_content as cache_content_sig, ccb.cache_updated as cache_updated_sig' );
				}
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

			/* Set vars */
			IPSSearch::$ask = $sortKey;
			IPSSearch::$aso = strtolower( $order_dir );
			IPSSearch::$ast = $sortType;
				
			/* If we are search in titles only, then the ID array will be TIDs */
			if( $content_title_only )
			{
				$k = 'tid';
				
				$this->DB->build( array( 
									'select'   => "t.*",
									'from'	   => array( 'topics' => 't' ),
		 							'where'	   => 't.tid IN( ' . implode( ',', $ids ) . ')',
									'add_join' => array_merge( array( array( 'select'	=> 'p.*',
																			 'from'		=> array( 'posts' => 'p' ),
															 				 'where'	=> 'p.pid=t.topic_firstpost',
															 				 'type'		=> 'left' ),
																	  array( 'select'	=> 'm.member_id, m.members_display_name, m.members_seo_name',
																			 'from'		=> array( 'members' => 'm' ),
															 				 'where'	=> 'm.member_id=p.author_id',
															 				 'type'		=> 'left' ) ), $_post_joins ) ) );
			}
			/* Otherwise, it's PIDs */
			else
			{
				$k = 'pid';
				
				$this->DB->build( array( 
									'select'   => "p.*",
									'from'	   => array( 'posts' => 'p' ),
		 							'where'	   => 'p.pid IN( ' . implode( ',', $ids ) . ')',
									'add_join' => array_merge( array( array( 'select'	=> 't.*',
																			 'from'		=> array( 'topics' => 't' ),
												 							 'where'	=> 't.tid=p.topic_id',
												 							 'type'		=> 'left' ),
																	  array( 'select'	=> 'm.member_id, m.members_display_name, m.members_seo_name',
																			 'from'		=> array( 'members' => 'm' ),
												 							 'where'	=> 'm.member_id=p.author_id',
												 							 'type'		=> 'left' ) ), $_post_joins ) ) );
															
			}
			
			/* Grab data */
			$this->DB->execute();
			
			/* Grab the results */
			while( $row = $this->DB->fetch() )
			{
				$_rows[ $row[ $k ] ] = $row;
			}
			
			/* Sort */
			if ( count( $_rows ) )
			{
				usort( $_rows, array("IPSSearch", "usort") );
		
				foreach( $_rows as $id => $row )
				{
					/* Prevent member from stepping on it */
					$row['topic_title'] = $row['title'];
					
					/* Got author but no member data? */
					if ( ! empty( $row['author_id'] ) )
					{
						$members[ $row['author_id'] ] = $row['author_id'];
					}
					
					/* Topic ids? */
					if ( ! empty( $row['topic_id'] ) )
					{
						$topicIds[ $row['topic_id'] ] = $row['topic_id'];
					}
				
					$row['cleanSearchTerm'] = urlencode($search_term);
					$row['topicPrefix']     = $row['pinned'] ? $this->registry->getClass('output')->getTemplate('forum')->topicPrefixWrap( $this->settings['pre_pinned'] ) : '';
					
					$row['_isVisible'] = ( $this->registry->getClass('class_forums')->fetchHiddenTopicType( $row ) == 'visible' ) ? true : false;
					$row['_isHidden']  = ( $this->registry->getClass('class_forums')->fetchHiddenTopicType( $row ) == 'hidden' ) ? true : false;
					$row['_isDeleted'] = ( $this->registry->getClass('class_forums')->fetchHiddenTopicType( $row ) == 'sdelete' ) ? true : false;
					
					$row['_p_isVisible'] = ( $this->registry->getClass('class_forums')->fetchHiddenType( $row ) == 'visible' ) ? true : false;
					$row['_p_isHidden']  = ( $this->registry->getClass('class_forums')->fetchHiddenType( $row ) == 'hidden' ) ? true : false;
					$row['_p_isDeleted'] = ( $this->registry->getClass('class_forums')->fetchHiddenType( $row ) == 'sdelete' ) ? true : false;
					
					/* Is the topic deleted? If so, then the first post will appear as such */
					if ( $row['_isDeleted'] AND $permissions['TopicSoftDeleteSee'] )
					{
						$row['_p_isDeleted']    = true;
						$_sdPids[ $row['pid'] ] = $row['pid'];
					}
					
					/* Collect TIDS of soft deleted topics */
					if ( $row['_isDeleted'] )
					{
						if ( $permissions['TopicSoftDeleteSee'] )
						{
							$_sdTids[ $row['tid'] ] = $row['tid'];
						}
						else
						{
							continue;
						}
					}
					
					/* Collect TIDS of soft deleted topics */
					if ( $row['_p_isDeleted'] )
					{
						if ( $permissions['PostSoftDeleteSee'] )
						{
							$_sdPids[ $row['pid'] ] = $row['pid'];
						}
						else
						{
							continue;
						}
					}
				
					$results[ $row['pid'] ] = $this->genericizeResults( $row );
				}
			}
			
			/* Need to load members? */
			if ( count( $members ) )
			{
				$mems = IPSMember::load( $members, 'all' );
				
				foreach( $results as $id => $r )
				{
					if ( ! empty( $r['author_id'] ) AND isset( $mems[ $r['author_id'] ] ) )
					{
						$mems[ $r['author_id'] ]['m_posts'] = $mems[ $r['author_id'] ]['posts'];
						//unset( $mems[ $r['author_id'] ]['posts'] );
						unset( $mems[ $r['author_id'] ]['last_post'] );
						
						if ( isset( $r['cache_content_sig'] ) )
						{ 
							$mems[ $r['author_id'] ]['cache_content'] = $r['cache_content_sig'];
							$mems[ $r['author_id'] ]['cache_updated'] = $r['cache_updated_sig'];
						}
						
						$_mem = IPSMember::buildDisplayData( $mems[ $r['author_id'] ], array( 'signature' => 1 ) );
						
						unset( $_mem['cache_content'], $_mem['cache_updated'] );
						
						$results[ $id ]['_realPosts']	= $results[ $id ]['posts'];
						$results[ $id ]					= array_merge( $results[ $id ], $_mem );
						$results[ $id ]['posts']		= $results[ $id ]['_realPosts'];
					}
				}
			}
			
			/* Generate 'dot' folder icon */
			if ( $this->settings['show_user_posted'] AND count( $topicIds ) )
			{
				$this->DB->build( array( 'select' => 'author_id, topic_id',
										 'from'   => 'posts',
										 'where'  => 'queued=0 AND author_id=' . $this->memberData['member_id'] . ' AND topic_id IN(' . implode( ',', $topicIds ) . ')' ) );
										  
				$this->DB->execute();
				
				while( $p = $this->DB->fetch() )
				{
					$dots[ $p['topic_id'] ] = 1;
				}
				
				/* Merge into results */
				foreach( $results as $id => $r )
				{
					if ( isset( $dots[ $r['topic_id'] ] ) )
					{
						$results[ $id ]['_hasPosted'] = 1;
					}
				}
			}
			
			/* Got any deleted items */
			if ( count( $_sdTids ) )
			{
				$sData = IPSDeleteLog::fetchEntries( $_sdTids, 'topic', false );
				
				if ( count( $sData ) )
				{
					foreach( $results as $id => $data )
					{
						if ( isset( $_sdTids[ $data['tid'] ] ) )
						{
							$results[ $id ]['sData']        = $sData[ $data['tid'] ];
							$results[ $id ]['permissions']  = $permissions;
						}
					}
				}
			}
			
			/* Got any deleted items */
			if ( count( $_sdPids ) )
			{
				$sData = IPSDeleteLog::fetchEntries( $_sdPids, 'post', false );
				
				if ( count( $sData ) )
				{
					foreach( $results as $id => $data )
					{
						if ( isset( $_sdPids[ $data['pid'] ] ) AND ! isset( $results[ $id ]['sData']) )
						{
							$results[ $id ]['sData']        = $sData[ $data['pid'] ];
							$results[ $id ]['permissions']  = $permissions;
						}
					}
				}
			}
		}
		
		return $results;
	}
	
	/**
	 * Reassigns fields in a generic way for results output
	 *
	 * @param  array  $r
	 * @return array
	 **/
	public function genericizeResults( $r )
	{
		$r['app']					= 'forums';
		$r['content']				= $r['post'];
		$r['content_title']			= $r['title'];
		$r['updated']				= $r['post_date'];
		$r['lastupdate']			= $r['last_post'];
		$r['type_2']				= 'topic';
		$r['type_id_2']				= $r['tid'];
		$r['misc']					= $r['pid'];

		return $r;
	}

}