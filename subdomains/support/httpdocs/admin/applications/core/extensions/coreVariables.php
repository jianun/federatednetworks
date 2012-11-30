<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Core registered caches, redirect resets and bitwise settings
 * Last Updated: $Date: 2010-04-23 19:39:24 -0400 (Fri, 23 Apr 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 6174 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * Which caches to load by default
 */
$_LOAD = array();

if ( $_GET['module'] == 'search' )
{
	$_LOAD['ranks']				= 1;
	$_LOAD['bbcode']			= 1;
	$_LOAD['emoticons']			= 1;
	$_LOAD['reputation_levels']	= 1;
	$_LOAD['attachtypes']		= 1;
}

if ( $_GET['module'] == 'reports' )
{
	$_LOAD['ranks']				= 1;
	$_LOAD['bbcode']			= 1;
	$_LOAD['emoticons']			= 1;
}

if( $_GET['module'] == 'usercp' )
{
	$_LOAD['ranks']				= 1;
	$_LOAD['reputation_levels']	= 1;
	$_LOAD['emoticons']         = 1;
}


/* Never, ever remove or re-order these options!!
 * Feel free to add, though. :) */

$_BITWISE = array();
