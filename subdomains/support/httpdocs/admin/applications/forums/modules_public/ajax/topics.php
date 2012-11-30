<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Ajax Functions For Topics
 * Last Updated: $Date: 2010-07-01 18:13:46 -0400 (Thu, 01 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Revision: 6596 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_forums_ajax_topics extends ipsAjaxCommand
{
	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs for the ajax handler]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$this->lang->loadLanguageFile( array( 'public_topic', 'public_mod' ), 'forums' );

		//-----------------------------------------
		// What to do?
		//-----------------------------------------
		
		switch( $this->request['do'] )
		{
			default:
			case 'editBoxShow':
				$this->editBoxShow();
			break;
			
			case 'editBoxSave':
				$this->editBoxSave();
			break;
			
			case 'saveTopicTitle':
				$this->saveTopicTitle();
			break;
			
			case 'saveTopicDescription':
				$this->saveTopicDescription();
			break;

			case 'rateTopic':
				$this->rateTopic();
			break;
			
			case 'postApproveToggle':
				$this->_postApproveToggle();
			break;
			
			case 'preview':
				$this->_topicPreview();
			break;
			
			case 'reputation':
				$this->reputation();
			break;
		}
	}
	
	/**
	 * Displays reputation popup
	 *
	 * @access	protected
	 * @return	void
	 **/
	protected function reputation()
	{
		if ( !$this->memberData['gbw_view_reps'] )
		{
			$this->returnJsonError('no_permission');
		}
	
		$postID = intval( $this->request['post'] );
		
		/* Get data */
		$reps = array();
		$this->DB->build( array(
			'select'	=> 'member_id, rep_rating',
			'from'		=> 'reputation_index',
			'where'		=> "app='forums' AND type='pid' AND type_id='{$postID}'",
			'order'		=> 'rep_date',
			) );
					
		$q = $this->DB->execute();
		while ( $r = $this->DB->fetch( $q ) )
		{
			$r['member'] = IPSMember::load( $r['member_id'] );
			$reps[] = $r;
		}
		
		return $this->returnHtml( $this->registry->output->getTemplate('topic')->reputationPopup( $reps ) );
	}
	
	/**
	 * Displays a topic preview
	 *
	 * @access	protected
	 * @return	void
	 **/
	protected function _topicPreview()
	{
		/* INIT */
		$tid   = intval( $this->request['tid'] );
		$pid   = intval( $this->request['pid'] );
		$sTerm = trim( $this->request['searchTerm'] );
		$topic = array();
		$posts = array();
		$query = '';
		
		/* Grab topic data and first post */
		$topic = $this->DB->buildAndFetch( array( 'select'   => 't.*, t.title as topic_title, t.posts as topic_posts, t.last_post as topic_last_post',
												  'from'     => array( 'topics' => 't' ),
												  'where'    => 't.tid=' . $tid,
												  'add_join' => array( array( 'select' => 'p.*',
												  							  'from'   => array( 'posts' => 'p' ),
												  							  'where'  => 'p.pid=topic_firstpost',
												  							  'type'   => 'left' ),
												  					   array( 'select' => 'm.*',
												  					   		  'from'   => array( 'members' => 'm' ),
												  					   		  'where'  => 'm.member_id=p.author_id',
												  					   		  'type'   => 'left' ),
												  					   array( 'select' => 'pp.*',
												  					   		  'from'   => array( 'profile_portal' => 'pp' ),
												  					   		  'where'  => 'm.member_id=pp.pp_member_id',
												  					   		  'type'   => 'left' ) ) ) );
		
		if ( ! $topic['tid'] OR ! $topic['pid'] )
		{
			return $this->returnString( 'no_topic' );
		}
		
		/* Permission check */
		if ( $this->registry->class_forums->forumsCheckAccess( $topic['forum_id'], 0, 'topic', $topic, true ) !== true )
		{
			return $this->returnHtml( $this->registry->output->getTemplate('global_other')->ajaxPopUpNoPermission() );
		}
		
		/* Build permissions */
	
		$permissions['PostSoftDeleteSee']      = $this->registry->getClass('class_forums')->canSeeSoftDeletedPosts( $topic['forum_id'] );
		$permissions['SoftDeleteContent']      = $this->registry->getClass('class_forums')->canSeeSoftDeleteContent( $topic['forum_id'] );
		$permissions['TopicSoftDeleteSee']     = $this->registry->getClass('class_forums')->canSeeSoftDeletedTopics( $topic['forum_id'] );
		$permissions['canQueue']			   = $this->registry->getClass('class_forums')->canQueuePosts( $topic['forum_id'] );
		
		/* Boring old boringness */
		if ( $permissions['canQueue'] )
		{
			if ( $permissions['PostSoftDeleteSee'] )
			{
				$query	= $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'hidden', 'sdeleted') ) . ' AND ';
			}
			else
			{
				$query	= $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'hidden') ) . ' AND ';
			}
		}
		else
		{
			if ( $permissions['PostSoftDeleteSee'] )
			{
				$query	= $this->registry->class_forums->fetchPostHiddenQuery(array('visible', 'sdeleted') ) . ' AND ';
			}
			else
			{
				$query	= $this->registry->class_forums->fetchPostHiddenQuery(array('visible') ) . ' AND ';
			}
		}
		
		/* Assign */
		$posts['first'] = $topic;
		
		/* Any more for any more? */
		if ( $topic['topic_posts'] )
		{
			/* Grab number of unread posts? */
			$last_time = $this->registry->classItemMarking->fetchTimeLastMarked( array( 'forumID' => $topic['forum_id'], 'itemID' => $tid ) );
			
			if ( $last_time AND $last_time < $topic['topic_last_post'] )
			{
				$count = $this->DB->buildAndFetch( array( 'select' => 'COUNT(*) as count, MAX(pid) as max, MIN(pid) as min',
												  		  'from'   => 'posts',
												          'where'  => $query . "topic_id={$tid} AND post_date > " . intval( $last_time ) )	);
			}
			else
			{
				$count = $this->DB->buildAndFetch( array( 'select' => 'MAX(pid) as max',
												  		  'from'   => 'posts',
												          'where'  => $query . "topic_id={$tid}" ) );
				$count['min']   = 0;
				$count['count'] = 0;
			}
											  
			$topic['_lastRead']    = $last_time;
			$topic['_unreadPosts'] = intval( $count['count'] );
			
			/* Got a max and min */
			if ( $count['max'] )
			{
				$this->DB->build( array(  'select'   => 'p.*',
										  'from'     => array( 'posts' => 'p' ),
										  'where'    => 'p.pid IN (' . intval( $count['min'] ) . ',' . intval( $count['max'] ) . ')',
										  'add_join' => array( array( 'select' => 'm.*',
										  					   		  'from'   => array( 'members' => 'm' ),
										  					   		  'where'  => 'm.member_id=p.author_id',
										  					   		  'type'   => 'left' ),
										  					   array( 'select' => 'pp.*',
										  					   		  'from'   => array( 'profile_portal' => 'pp' ),
										  					   		  'where'  => 'm.member_id=pp.pp_member_id',
										  					   		  'type'   => 'left' ) ) ) );
										  					   		  
				$this->DB->execute();
				
				while( $r = $this->DB->fetch() )
				{
					if ( $r['pid'] == $count['max'] )
					{
						$posts['last'] = $r;
					}
					else
					{
						$posts['unread'] = $r;
					}
				}
			}
			
			if ( is_array( $posts['unread'] ) AND is_array( $posts['last'] ) )
			{
				if ( $posts['unread']['pid'] == $posts['last']['pid'] )
				{
					unset( $posts['unread'] );
				}
				else if ( $posts['unread']['pid'] == $posts['first']['pid'] )
				{
					unset( $posts['unread'] );
				}
				
			}
		}
		
		/* Search? */
		if ( $pid AND $sTerm )
		{
			$this->DB->build( array(  'select'   => 'p.*',
									  'from'     => array( 'posts' => 'p' ),
									  'where'    => 'p.pid=' . $pid,
									  'add_join' => array( array( 'select' => 'm.*',
									  					   		  'from'   => array( 'members' => 'm' ),
									  					   		  'where'  => 'm.member_id=p.author_id',
									  					   		  'type'   => 'left' ),
									  					   array( 'select' => 'pp.*',
									  					   		  'from'   => array( 'profile_portal' => 'pp' ),
									  					   		  'where'  => 'm.member_id=pp.pp_member_id',
									  					   		  'type'   => 'left' ) ) ) );
										  					   		  
			$this->DB->execute();
			
			$posts['search'] = $this->DB->fetch();
		}
		
		/* Still here? */
		foreach( $posts as $k => $data )
		{
			$data  = IPSMember::buildDisplayData( $data );
			
			/* Search term? */
			if ( $k == 'search' AND $pid AND $sTerm )
			{
				$data['post'] = IPSText::truncateTextAroundPhrase( IPSText::getTextClass( 'bbcode' )->stripAllTags( str_replace( '<br />', ' ', $data['post'] ) ), $sTerm );
				$data['post'] = IPSText::searchHighlight( $data['post'], $sTerm );
			}
			else
			{
				$data['post'] = IPSText::truncate( IPSText::getTextClass( 'bbcode' )->stripAllTags( $data['post'] ), 500 );
			}
			
			$data['_isVisible']		   = ( $this->registry->getClass('class_forums')->fetchHiddenType( $data ) == 'visible' ) ? true : false;
			$data['_isHidden']		   = ( $this->registry->getClass('class_forums')->fetchHiddenType( $data ) == 'hidden' ) ? true : false;
			$data['_isDeleted']		   = ( $this->registry->getClass('class_forums')->fetchHiddenType( $data ) == 'sdelete' ) ? true : false;

			$posts[ $k ] = $data;
		}
		
		$topic['_key'] = uniqid(microtime());
		
		return $this->returnHtml( $this->registry->output->getTemplate('topic')->topicPreview( $topic, $posts ) );
	}
	
	/**
	 * Toggle the posts approve thingy
	 *
	 * @access	protected
	 * @return	void
	 **/
	protected function _postApproveToggle()
	{
		/* INIT */
		$topicID  = intval( $this->request['t'] );
		$postID   = intval( $this->request['p'] );
		$approve  = ( $this->request['approve'] == 1 ) ? TRUE : FALSE;
		$_yoGo    = FALSE;
		
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . '/sources/classes/moderate.php', 'moderatorLibrary', 'forums' );
        $_modLibrary = new $classToLoad( $this->registry );
                
		/* Load topic */
		$topic = $this->DB->buildAndFetch( array( 'select' => '*',
												  'from'   => 'topics',
												  'where'  => 'tid=' . $topicID ) );
		
		if ( ! $topic['tid'] )
		{
			$this->returnJsonArray( array( 'error' => 'notopic' ) );
		}
		
		/* Permission Checks */
		if ( $this->memberData['g_is_supmod'] )
		{
			$_yoGo = TRUE;
		}
		else if ( is_array( $this->memberData['forumsModeratorData'] ) AND $this->memberData['forumsModeratorData'][ $topic['forum_id'] ]['post_q'] )
		{
			$_yoGo = TRUE;
		}

		if ( ! $_yoGo )
		{
			$this->returnJsonArray( array( 'error' => 'nopermission' ) );
		}
		
		$_modLibrary->postToggleApprove( array( $postID ), $approve, $topicID );
		
		$this->returnJsonArray( array( 'status' => 'ok', 'postApproved' => $approve ) );
	}
	
	/**
	 * Add vote to rating
	 *
	 * @access	public
	 * @return	void
	 **/
	public function rateTopic()
	{
		/* INIT */
		$topic_id  = intval( $this->request['t'] );
		$rating_id = intval( $this->request['rating'] );
		$vote_cast = array();
		
		IPSDebug::fireBug( 'info', array( 'The topic rating request has been received...' ) );
		
		/* Query topic */
		$topic_data = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'topics', 'where' => "tid={$topic_id}" ) );
		
		/* Make sure we have a valid topic id */
		if( ! $topic_data['tid'] )
		{
			IPSDebug::fireBug( 'error', array( 'The topic was not found in the database' ) );
			$this->returnJsonArray( array( 'error_key' => 'topics_no_tid', 'error_code' => 10346 ) );
		}
		
		if( $topic_data['state'] != 'open' )
		{
			IPSDebug::fireBug( 'error', array( 'The topic is not open' ) );
			
			$this->returnJsonArray( array( 'error_key' => 'topic_rate_locked', 'error_code' => 10348 ) );
		}					
		
		/* Query Forum */
		$forum_data = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'forums', 'where' => "id={$topic_data['forum_id']}" ) );
				
		/* Permission Check */
		$can_rate = ( $forum_data['forum_allow_rating'] && $this->memberData['member_id'] && $this->memberData['g_topic_rate_setting'] ) ? 1 : 0;
		
		if( ! $can_rate )
		{
			IPSDebug::fireBug( 'error', array( 'The user cannot rate topics in this forum' ) );
			
			$this->returnJsonArray( array( 'error_key' => 'topic_rate_no_perm', 'error_code' => 10345 ) );
			exit();
		}
   		
		/* Sneaky members rating topic more than 5? */		
   		if( $rating_id > 5 )
   		{
	   		$rating_id = 5;
   		}
   		
   		if( $rating_id < 0 )
   		{
	   		$rating_id = 0;
   		}
   		
		/* Have we rated before? */
		$rating = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'topic_ratings', 'where' => "rating_tid={$topic_data['tid']} and rating_member_id=".$this->memberData['member_id'] ) );
		
		/* Already rated? */
		if( $rating['rating_id'] )
		{
			/* Do we allow re-ratings? */
			if( $this->memberData['g_topic_rate_setting'] == 2 )
			{
				if( $rating_id != $rating['rating_value'] )
				{
					$new_rating = $rating_id - $rating['rating_value'];
					
					$this->DB->update( 'topic_ratings', array( 'rating_value' => $rating_id ), 'rating_id=' . $rating['rating_id'] );
					
					$this->DB->update( 'topics', array( 'topic_rating_total' => intval( $topic_data['topic_rating_total'] ) + $new_rating ), 'tid=' . $topic_data['tid'] );
				}
				
				IPSDebug::fireBug( 'info', array( 'The rating was updated' ) );
				
				$this->returnJsonArray( array(
										'rated'				 => 'update',
										'message'			 => $this->lang->words['topic_rating_changed'],
										'topic_rating_total' => intval( $topic_data['topic_rating_total'] ) + $new_rating,
										'topic_rating_hits'	 => $topic_data['topic_rating_hits']
								) 	);
			}
			else
			{
				IPSDebug::fireBug( 'warn', array( 'The user is not allowed to update their rating' ) );
				
				$this->returnJsonArray( array( 'error_key' => 'topic_rated_already', 'error_code' => 0 ) );
			}
		}
		/* NEW RATING! */
		else
		{
			$this->DB->insert( 'topic_ratings', array( 
														'rating_tid'        => $topic_data['tid'],
														'rating_member_id'  => $this->memberData['member_id'],
														'rating_value'      => $rating_id,
														'rating_ip_address' => $this->member->ip_address 
													) 
							);
																	
			$this->DB->update( 'topics', array( 
													'topic_rating_hits'  => intval( $topic_data['topic_rating_hits'] )  + 1,
													'topic_rating_total' => intval( $topic_data['topic_rating_total'] ) + $rating_id 
												), 'tid='.$topic_data['tid'] );

			IPSDebug::fireBug( 'info', array( 'The rating was inserted' ) );
			
			$this->returnJsonArray( array( 
									'rated'				 => 'new',
									'message'			 => $this->lang->words['topic_rating_done'],
									'topic_rating_total' => intval( $topic_data['topic_rating_total'] ) + $rating_id ,
									'topic_rating_hits'	 => intval( $topic_data['topic_rating_hits'] )  + 1,
									'_rate_int'			 => round( (intval( $topic_data['topic_rating_total'] ) + $rating_id) / (intval( $topic_data['topic_rating_hits'] ) + 1) )
							) 	);
		}
	}	
	
	/**
	 * Saves a ajax topic title edit
	 *
	 * @access	public
	 * @return	void
	 **/
	public function saveTopicTitle()
	{
		/* INIT */
		IPSDebug::fireBug( 'info', array( 'Initial name: ' . $_POST['name'] ) );
		$name	   = $_POST['name'];
		IPSDebug::fireBug( 'info', array( 'after convert and make safe: ' . $name ) );
		$tid	   = intval( $this->request['tid'] );
		$can_edit  = 0;
		
		IPSDebug::fireBug( 'info', array( 'The topic title after converting is: ' . $name ) );

		/* Check ID */
		if( ! $tid )
		{ 
			$this->returnJsonError( $this->lang->words['ajax_no_topic_id'] );
		}
		
		/* Load Topic */
		$topic = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'topics', 'where' => 'tid=' . $tid ) );
		
		if( ! $topic['tid'] )
		{ 
			$this->returnJsonError( $this->lang->words['ajax_topic_not_found'] );
		}
		
		/* Check Permissions */
		if ( $this->memberData['g_is_supmod'] )
		{
			$can_edit = 1;
		}
		
		else if( is_array( $this->memberData['forumsModeratorData'] ) AND $this->memberData['forumsModeratorData'][ $topic['forum_id'] ]['edit_topic'] )
		{
			$can_edit = 1;
		}

		if( ! $can_edit )
		{
			$this->returnJsonError( $this->lang->words['ajax_no_t_permission'] );
		}

		/* Make sure we have a valid name */
		if( trim( $name ) == '' || ! $name )
		{
			$this->returnJsonError( $this->lang->words['ajax_no_t_name'] );
			exit();
		}
		
		/* Clean */
		if( $this->settings['etfilter_shout'] )
		{
			if( function_exists('mb_convert_case') )
			{
				if( in_array( strtolower( $this->settings['gb_char_set'] ), array_map( 'strtolower', mb_list_encodings() ) ) )
				{
					$name = mb_convert_case( $name, MB_CASE_TITLE, $this->settings['gb_char_set'] );
				}
				else
				{
					$name = ucwords( strtolower($name) );
				}
			}
			else
			{
				$name = ucwords( strtolower($name) );
			}
		}
		
		$name		= IPSText::parseCleanValue( $name );
		$name		= $this->cleanTopicTitle( $name );
		$name		= IPSText::getTextClass( 'bbcode' )->stripBadWords( $name );
		$title_seo	= IPSText::makeSeoTitle( $name, TRUE );

		/* Update the topic */
		$this->DB->update( 'topics', array( 'title' => $name, 'title_seo' => $title_seo ), 'tid='.$tid );
		
		$this->DB->insert( 'moderator_logs', array(
											  		'forum_id'		=> intval( $topic['forum_id'] ),
											  		'topic_id'		=> $tid,
											  		'member_id'		=> $this->memberData['member_id'],
											  		'member_name'	=> $this->memberData['members_display_name'],
											  		'ip_address'	=> $this->member->ip_address,
											 		'http_referer'	=> htmlspecialchars( getenv('HTTP_REFERER') ),
											  		'ctime'			=> time(),
											  		'topic_title'	=> $name,
											  		'action'		=> sprintf( $this->lang->words['ajax_topictitle'], $topic['title'], $name),
											  		'query_string'	=> htmlspecialchars( getenv('QUERY_STRING') ),
										  )  );			
		
		/* Update the last topic title? */
		if ( $topic['tid'] == $this->registry->class_forums->forum_by_id[ $topic['forum_id'] ]['last_id'] )
		{
			$this->DB->update( 'forums', array( 'last_title' => $name, 'seo_last_title' => $title_seo ), 'id=' . $topic['forum_id'] );
		}
		
		if ( $topic['tid'] == $this->registry->class_forums->forum_by_id[ $topic['forum_id'] ]['newest_id'] )
		{
			$this->DB->update( 'forums', array( 'newest_title' => $name ), 'id=' . $topic['forum_id'] );
		}	

		/* All Done */
		$this->returnJsonArray( array( 'title' => $name, 'url' => $this->registry->output->buildSEOUrl( 'showtopic=' . $tid, 'public', $title_seo, 'showtopic' ) ) );
	}
	
	/**
	 * Clean the topic title
	 *
	 * @access	public
	 * @param	string	Raw title
	 * @return	string	Cleaned title
	 */
	public function cleanTopicTitle( $title="" )
	{
		if( $this->settings['etfilter_punct'] )
		{
			$title	= preg_replace( "/\?{1,}/"      , "?"    , $title );		
			$title	= preg_replace( "/(&#33;){1,}/" , "&#33;", $title );
		}

		//-----------------------------------------
		// The DB column is 250 chars, so we need to do true mb_strcut, then fix broken HTML entities
		// This should be fine, as DB would do it regardless (cept we can fix the entities)
		//-----------------------------------------

		$title = preg_replace( "/&(#{0,}([a-zA-Z0-9]+?)?)?$/", '', IPSText::mbsubstr( $title, 0, 250 ) );
		
		$title = IPSText::stripAttachTag( $title );
		$title = str_replace( "<br />", "", $title  );
		$title = trim( $title );

		return $title;
	}
	
	/**
	 * Saves a ajax topic description edit
	 *
	 * @access	public
	 * @return	void
	 **/
	public function saveTopicDescription()
	{
		/* INIT */
		$description = $this->convertAndMakeSafe( $this->request['description'], TRUE );
		$tid	     = intval( $this->request['tid'] );
		$can_edit    = 0;

		/* Check ID */
		if( ! $tid )
		{ 
			$this->returnString( $this->lang->words['ajax_no_topic_id'] );
		}
		
		/* Load Topic */
		$topic = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'topics', 'where' => 'tid=' . $tid ) );
		
		if( ! $topic['tid'] )
		{ 
			$this->returnString( $this->lang->words['ajax_topic_not_found'] );
		}
		
		/* Check Permissions */
		if ( $this->memberData['g_is_supmod'] )
		{
			$can_edit = 1;
		}
		
		if( $this->memberData['forumsModeratorData'][ $topic['forum_id'] ]['edit_topic'] )
		{
			$can_edit = 1;
		}

		if( ! $can_edit )
		{
			$this->class_ajax->returnString( $this->lang->words['ajax_no_t_permission'] );
		}
		
		/* Update the description */
    	$this->DB->update( 'topics', array( 'description' => $description ), 'tid=' . $tid );
    	
		$this->DB->insert( 'moderator_logs', array(
											  		'forum_id'    => intval( $topic['forum_id'] ),
											  		'topic_id'    => $tid,
											  		'member_id'   => $this->memberData['member_id'],
											  		'member_name' => $this->memberData['members_display_name'],
											  		'ip_address'  => $this->input['IP_ADDRESS'],
											 		'http_referer'=> htmlspecialchars( getenv('HTTP_REFERER') ),
											  		'ctime'       => time(),
											  		'topic_title' => $topic['title'],
											  		'action'      => $this->lang->words['ajax_topicdesc'],
											  		'query_string'=> htmlspecialchars( getenv('QUERY_STRING') ),
										  )  );   
		
		/* All Done */
		$this->returnString( 'success' );
	}	

	/**
	 * Saves the post
	 *
	 * @access	public
	 * @return	void
	 */
	public function editBoxSave()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$pid		   = intval( $this->request['p'] );
		$fid		   = intval( $this->request['f'] );
		$tid		   = intval( $this->request['t'] );
		$attach_pids   = array();

   		$this->request['post_edit_reason'] = $this->convertAndMakeSafe( $_POST['post_edit_reason'] );

   		//-----------------------------------------
		// Set things right
		//-----------------------------------------
		
		$this->request['Post'] =  IPSText::parseCleanValue( $_POST['Post'] );

		//-----------------------------------------
		// Check P|T|FID
		//-----------------------------------------

		if ( ! $pid OR ! $tid OR ! $fid )
		{
			$this->returnString( 'error' );
		}
		
		if ( $this->memberData['member_id'] )
		{
			if ( $this->memberData['restrict_post'] )
			{
				if ( $this->memberData['restrict_post'] == 1 )
				{
					$this->returnString( 'nopermission' );
				}

				$post_arr = IPSMember::processBanEntry( $this->memberData['restrict_post'] );

				if ( time() >= $post_arr['date_end'] )
				{
					//-----------------------------------------
					// Update this member's profile
					//-----------------------------------------
					
					IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'restrict_post' => 0 ) ) );
				}
				else
				{
					$this->returnString( 'nopermission' );
				}
			}
		}

		//-----------------------------------------
		// Load Lang
		//-----------------------------------------

		$this->registry->getClass( 'class_localization')->loadLanguageFile( array( 'public_topics' ) );

		if ( ! is_object( $this->postClass ) )
		{
			require_once( IPSLib::getAppDir( 'forums' ) . '/sources/classes/post/classPost.php' );
			$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . '/sources/classes/post/classPostForms.php', 'classPostForms', 'forums' );
                        
			$this->postClass =  new $classToLoad( $this->registry );
		}
		
		# Forum Data
		$this->postClass->setForumData( $this->registry->getClass('class_forums')->forum_by_id[ $fid ] );
		
		# IDs
		$this->postClass->setTopicID( $tid );
		$this->postClass->setPostID( $pid );
		$this->postClass->setForumID( $fid );
		
		if( isset($this->request['post_htmlstatus']) )		// Off is "0"
		{
			$this->postClass->setSettings( array( 'post_htmlstatus' => $this->request['post_htmlstatus'] ) );
		}
		
		/* Topic Data */
		$this->postClass->setTopicData( $this->DB->buildAndFetch( array( 
																			'select'   => 't.*, p.poll_only', 
																			'from'     => array( 'topics' => 't' ), 
																			'where'    => "t.forum_id={$fid} AND t.tid={$tid}",
																			'add_join' => array(
																								array( 
																										'type'	=> 'left',
																										'from'	=> array( 'polls' => 'p' ),
																										'where'	=> 'p.tid=t.tid'
																									)
																								)
									) 							)	 );
		# Set Author
		$this->postClass->setAuthor( $this->member->fetchMemberData() );
		
		# Set from ajax
		$this->postClass->setIsAjax( TRUE );

		# Post Content
		$this->postClass->setPostContent( $_POST['Post'] );

		# Get Edit form
		try
		{
			/**
			 * If there was an error, return it as a JSON error
			 */
			if ( $this->postClass->editPost() === FALSE )
			{
				$this->returnJsonError( $this->postClass->getPostError() );
			}
			
			$topic = $this->postClass->getTopicData();
			$post  = $this->postClass->getPostData();
			
			//-----------------------------------------
			// Pre-display-parse
			//-----------------------------------------
			
			IPSText::getTextClass( 'bbcode' )->parse_smilies			= $post['use_emo'];
			IPSText::getTextClass( 'bbcode' )->parse_html				= ( $this->registry->getClass('class_forums')->forum_by_id[ $fid ]['use_html'] and $this->memberData['g_dohtml'] and $post['post_htmlstate'] ) ? 1 : 0;
			IPSText::getTextClass( 'bbcode' )->parse_nl2br				= $post['post_htmlstate'] == 2 ? 1 : 0;
			IPSText::getTextClass( 'bbcode' )->parse_bbcode				= $this->registry->getClass('class_forums')->forum_by_id[ $fid ]['use_ibc'];
			IPSText::getTextClass( 'bbcode' )->parsing_section			= 'topics';
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $this->postClass->getAuthor('member_group_id');
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $this->postClass->getAuthor('mgroup_others');
				
			$post['post']	= IPSText::getTextClass( 'bbcode' )->preDisplayParse( $post['post'] );

			if ( IPSText::getTextClass( 'bbcode' )->error )
			{
				$this->returnJsonError( $this->lang->words[ IPSText::getTextClass( 'bbcode' )->error ] );
			}			

			$edit_by	= '';
			
			if ( $post['append_edit'] == 1 AND $post['edit_time'] AND $post['edit_name'] )
			{
				$e_time		= $this->registry->getClass( 'class_localization')->getDate( $post['edit_time'] , 'LONG' );
				$edit_by	= sprintf( $this->lang->words['edited_by'], $post['edit_name'], $e_time );
			}
			
			/* Attachments */
			if ( ! is_object( $this->class_attach ) )
			{
				//-----------------------------------------
				// Grab render attach class
				//-----------------------------------------

				require_once( IPSLib::getAppDir('core') . '/sources/classes/attach/class_attach.php' );
				$this->class_attach  =  new class_attach( $this->registry );
			}

			$this->class_attach->type  = 'post';
			$this->class_attach->init();

			$attachHtml             = $this->class_attach->renderAttachments( array( $pid => $post['post'] ) );
			$post['post']           = $attachHtml[ $pid ]['html'];
			$post['attachmentHtml'] = $attachHtml[ $pid ]['attachmentHtml'];
			
			$output		= $this->registry->output->getTemplate('topic')->quickEditPost( array(
																							'post'				=> $this->registry->getClass('output')->replaceMacros( IPSText::stripAttachTag( $post['post'] ) ),
																							'attachmentHtml'    => $post['attachmentHtml'],
																							'pid'				=> $pid,
																							'edit_by'			=> $edit_by,
																							'post_edit_reason'	=> $post['post_edit_reason']
																					) 		);

			//-----------------------------------------
			// Return plain text
			//-----------------------------------------

			$this->returnJsonArray( array( 'successString' => $output ) );
		}
		catch ( Exception $error )
		{
			$this->returnJsonError( $error->getMessage() );
		}
	}
	
	/**
	 * Shows the edit box
	 *
	 * @access	public
	 * @return	void
	 */
	public function editBoxShow()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$pid		 = intval( $this->request['p'] );
		$fid		 = intval( $this->request['f'] );
		$tid		 = intval( $this->request['t'] );
		$show_reason = 0;

		//-----------------------------------------
		// Check P|T|FID
		//-----------------------------------------
		
		if ( ! $pid OR ! $tid OR ! $fid )
		{
			$this->returnString( 'error' );
		}
		
		if ( $this->memberData['member_id'] )
		{
			if ( $this->memberData['restrict_post'] )
			{
				if ( $this->memberData['restrict_post'] == 1 )
				{
					$this->returnString( 'nopermission' );
				}
				
				$post_arr = IPSMember::processBanEntry( $this->memberData['restrict_post'] );
				
				if ( time() >= $post_arr['date_end'] )
				{
					//-----------------------------------------
					// Update this member's profile
					//-----------------------------------------
					
					IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'restrict_post' => 0 ) ) );
				}
				else
				{
					$this->returnString( 'nopermission' );
				}
			}
		}

		//-----------------------------------------
		// Get classes
		//-----------------------------------------
		
		if ( ! is_object( $this->postClass ) )
		{
			require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/post/classPost.php" );
			require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/post/classPostForms.php" );
			
			$this->registry->getClass( 'class_localization')->loadLanguageFile( array( 'public_editors' ), 'core' );
			
			$this->postClass		   =  new classPostForms( $this->registry );
		}
		
		# Forum Data
		$this->postClass->setForumData( $this->registry->getClass('class_forums')->forum_by_id[ $fid ] );
		
		# IDs
		$this->postClass->setTopicID( $tid );
		$this->postClass->setPostID( $pid );
		$this->postClass->setForumID( $fid );
		
		/* Topic Data */
		$this->postClass->setTopicData( $this->DB->buildAndFetch( array( 
																			'select'   => 't.*, p.poll_only', 
																			'from'     => array( 'topics' => 't' ), 
																			'where'    => "t.forum_id={$fid} AND t.tid={$tid}",
																			'add_join' => array(
																								array( 
																										'type'	=> 'left',
																										'from'	=> array( 'polls' => 'p' ),
																										'where'	=> 'p.tid=t.tid'
																									)
																								)
									) 							)	 );
		
		# Set Author
		$this->postClass->setAuthor( $this->member->fetchMemberData() );
		
		# Get Edit form
		try
		{
			$html = $this->postClass->displayAjaxEditForm();
			
			$html = $this->registry->output->replaceMacros( $html );

			$this->returnHtml( $html );
		}
		catch ( Exception $error )
		{
			$this->returnString( $error->getMessage() );
		}
	}
}