<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Redirect old lofi search results to the new IP.Board 3 urls
 * Last Updated: $Date: 2010-05-03 19:34:02 -0400 (Mon, 03 May 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6205 $
 *
 */

define( 'IPS_PUBLIC_SCRIPT', 'index.php' );

require_once( '../initdata.php' );
require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );

/* INIT Registry */
$reg = ipsRegistry::instance();
$reg->init();

/* GET INPUT */
$url    = my_getenv('REQUEST_URI') ? my_getenv('REQUEST_URI') : my_getenv('PHP_SELF');
$qs     = my_getenv('QUERY_STRING');
$link   = 'act=idx';
$id     = 0;
$st     = 0;

$justKeepMe = str_replace( '.html', '', ( $qs ) ? $qs : str_replace( "/", "", strrchr( $url, "/" ) ) );

/* Got pages? */
if ( strstr( $justKeepMe, "-" ) )
{
	list( $_mainBit, $_startBit ) = explode( "-", $justKeepMe );
	
	$justKeepMe = $_mainBit;
	$st         = intval( $_startBit );
}

if ( strstr( $justKeepMe, 't' ) AND is_numeric( substr( $justKeepMe, 1 ) ) )
{
	$id = intval( substr( $justKeepMe, 1 ) );
	
	$link = 'showtopic=' . $id;
	
	if ( $st )
	{
		$link .= '&amp;st=' . $st;
	}
}
else if ( strstr( $justKeepMe, 'f' ) AND is_numeric( substr( $justKeepMe, 1 ) ) )
{
	$id  = intval( substr( $justKeepMe, 1 ) );
	
	$link = 'showforum=' . $id;
	
	if ( $st )
	{
		$link .= '&amp;st=' . $st;
	}
}

/* GO GADGET GO */
if ( isset( $_SERVER['SERVER_PROTOCOL'] ) AND strstr( $_SERVER['SERVER_PROTOCOL'], '/1.0' ) )
{
	header("HTTP/1.0 301 Moved Permanently");
}
else
{
	header("HTTP/1.1 301 Moved Permanently");
}

header("Location: " . $reg->output->formatUrl( $reg->output->buildUrl( $link, 'public' ) ) );

exit();