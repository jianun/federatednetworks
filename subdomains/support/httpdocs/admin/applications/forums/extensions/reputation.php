<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Reputation configuration for application
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5713 $ 
 **/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$rep_author_config = array( 
						'pid' => array( 'column' => 'author_id', 'table'  => 'posts' )
					);
					
/*
 * The following config items are for the log viewer in the ACP */
					
$rep_log_joins = array(
						array(
								'from'   => array( 'posts' => 'p' ),
								'where'  => 'r.type="pid" AND r.type_id=p.pid and r.app="forums"',
								'type'   => 'left'
							),
						array(
								'select' => 't.title as repContentTitle, t.tid as repContentID',
								'from'   => array( 'topics' => 't' ),
								'where'  => 'p.topic_id=t.tid',
								'type'   => 'left'
							),
					);

$rep_log_where = "p.author_id=%s";

$rep_log_link = 'findpost=%d';