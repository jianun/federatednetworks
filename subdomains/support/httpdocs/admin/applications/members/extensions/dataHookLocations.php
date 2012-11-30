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

	/* MESSENGER DATA LOCATIONS */
	array( 'messengerSendReplyData', 'Messenger: Reply data'),
	array( 'messengerSendTopicData', 'Messenger: New conversation, topic data' ),
	array( 'messengerSendTopicFirstPostData', 'Messenger: New conversation, first post' ),
	
	/* PROFILE DATA LOCATIONS */
	array( 'profileCommentNew', 'Profile: New comment' ),
	array( 'profileFriendsNew', 'Profile: New friend' ),
	
	/* OUTPUT ARRAYS */
	array( 'memberListData', 'Member List View Output' ),
	array( 'onlineUsersListData', 'Online Users List Output' ),
	
);