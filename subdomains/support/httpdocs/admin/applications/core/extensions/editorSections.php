<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * BBCode Management : Determines if bbcode can be used in this section
 * Last Updated: $LastChangedDate: 2010-01-21 03:39:17 -0500 (Thu, 21 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5732 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

/*
 * An array of key => value pairs
 * When going to parse, the key should be passed to the editor
 *  to determine which bbcodes should be parsed in the section
 *
 */
$BBCODE	= array(
				'reports'			=> ipsRegistry::getClass('class_localization')->words['ctype__reports'],
				);