<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Define data hook locations
 * Last Updated: $Date: 2010-07-02 05:27:31 -0400 (Fri, 02 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6597 $
 *
 */

$dataHookLocations = array(

	/* POSTING LIBRARY DATA LOCATIONS */
	array( 'postAddReply', 'Add Reply' ),
	array( 'postAddReplyPoll','Add Reply: Poll' ),
	array( 'postAutoMerge', 'Add Reply: Auto merge with previous post' ),
	array( 'postAddReplyTopicUpdate', 'Add Reply: Topic Data' ),
	array( 'postAddTopic', 'New Topic: Topic Data' ),
	array( 'postFirstPost', 'New Topic: First Post' ),
	array( 'postAddTopicPoll', 'New Topic: Poll' ),
	array( 'editPostAddPoll', 'Edit Post: Added Poll' ),
	array( 'editPostUpdatePoll', 'Edit Post: Updated Poll' ),
	array( 'editPostUpdateTopicTitle', 'Edit Post: Update Topic Title'),
	array( 'editPostData', 'Edit Post: Post Data' ),
	array( 'updateForumLastPostData', 'Forum last post update data' ),
	
	/* OUTPUT ARRAYS */
	array( 'boardIndexCategories', 'Board Index Output: Categories' ),
	array( 'boardIndexOnlineUsers', 'Board Index Output: Active users' ),
	array( 'forumViewData', 'Forum View Output' ),
	array( 'topicViewPostData', 'Topic View Output: Posts Data' ),
	array( 'topicViewForumData', 'Topic View Output: Forum Data' ),
	array( 'topicViewTopicData', 'Topic View Output: Topic Data' ),
	array( 'topicViewDisplayData', 'Topic View Output: Other Data' ),
	
);