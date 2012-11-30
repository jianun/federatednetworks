<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Config file
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

/* Can search with this app */
$CONFIG['can_search']	      = 1;

/* Can view new content with this app */
$CONFIG['can_viewNewContent'] = 0;

/* Can fetch active content with this app */
$CONFIG['can_activeContent']  = 0;

/* Can fetch user generated content */
$CONFIG['can_userContent'] = 0;