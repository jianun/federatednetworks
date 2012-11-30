<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Sphinx template file
 * Last Updated: $Date: 2010-06-22 03:39:59 -0400 (Tue, 22 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6566 $
 * @since		3.0.0
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$appSphinxTemplate	= <<<EOF

################################# --- FORUM --- ##############################
source forums_search_posts_main : ipb_source_config
{
	# Set our forum PID counter
	sql_query_pre	= REPLACE INTO <!--SPHINX_DB_PREFIX-->cache_store VALUES( 'sphinx_forums_counter_posts', (SELECT max(pid) FROM <!--SPHINX_DB_PREFIX-->posts), '', 0, UNIX_TIMESTAMP() )
	
	# Query posts for the main source
	sql_query		= SELECT p.pid, p.pid as search_id, p.author_id, p.post_date, p.post, p.topic_id, p.queued, \
							 t.tid, t.title, t.title as tordinal, t.views, t.posts, t.forum_id, t.last_post, t.state, t.start_date, t.starter_id, t.last_poster_id, t.topic_firstpost, \
							CASE WHEN t.approved = -1 THEN 1 ELSE 0 END AS soft_deleted, \
							CASE WHEN t.approved = -1 THEN 0 ELSE t.approved END AS approved, \
							CONCAT(t.last_post, '.', t.tid ) as last_post_group \
					  FROM <!--SPHINX_DB_PREFIX-->posts p \
					  LEFT JOIN <!--SPHINX_DB_PREFIX-->topics t ON ( p.topic_id=t.tid )
	
	# Fields	
	sql_attr_uint			= queued
	sql_attr_uint			= approved
	sql_attr_uint			= soft_deleted
	sql_attr_uint			= search_id
	sql_attr_uint			= forum_id
	sql_attr_timestamp	    = post_date
	sql_attr_timestamp	    = last_post
	sql_attr_timestamp	    = start_date
	sql_attr_uint			= author_id
	sql_attr_uint			= starter_id
	sql_attr_uint			= tid
	sql_attr_uint			= posts
	sql_attr_uint			= views
	sql_attr_str2ordinal	= tordinal
	sql_attr_uint			= last_post_group
	
	sql_ranged_throttle	= 0
}

source forums_search_posts_delta : forums_search_posts_main
{
	# Override the base sql_query_pre
	sql_query_pre = 
	
	# Query posts for the delta source
	sql_query		= SELECT p.pid, p.pid as search_id, p.author_id, p.post_date, p.post, p.topic_id, p.queued, \
							 t.tid, t.title, t.title as tordinal, t.views, t.posts, t.forum_id, t.last_post, t.state, t.start_date, t.starter_id, t.last_poster_id, t.topic_firstpost, \
							 CASE WHEN t.approved = -1 THEN 1 ELSE 0 END AS soft_deleted, \
						 	 CASE WHEN t.approved = -1 THEN 0 ELSE t.approved END AS approved, \
							CONCAT(t.last_post, '.', t.tid ) as last_post_group \
					  FROM <!--SPHINX_DB_PREFIX-->posts p \
					  LEFT JOIN <!--SPHINX_DB_PREFIX-->topics t ON ( p.topic_id=t.tid ) \
					  WHERE p.pid > ( SELECT cs_value FROM <!--SPHINX_DB_PREFIX-->cache_store WHERE cs_key='sphinx_forums_counter_posts' )
}

index forums_search_posts_main
{
	source			= forums_search_posts_main
	path			= <!--SPHINX_BASE_PATH-->/forums_search_posts_main
	
	docinfo			= extern
	mlock			= 0
	morphology		= none
	min_word_len	= 2
	charset_type	= sbcs
	html_strip		= 0
	#infix_fields    = post, title
	#min_infix_len   = 3
	#enable_star     = 1
}

index forums_search_posts_delta : forums_search_posts_main
{
   source			= forums_search_posts_delta
   path				= <!--SPHINX_BASE_PATH-->/forums_search_posts_delta
}


EOF;
