<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Some required extensions to check for
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		Matt Mecham
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		1st December 2008
 * @version		$Revision: 5713 $
 *
 */
 
$INSTALLDATA = array(
	
array( 'prettyname'		=> "DOM XML Handling",
	   'extensionname'	=> "libxml2",
	   'helpurl'		=> "http://www.php.net/manual/en/dom.setup.php",
	   'testfor'		=> 'dom',
	   'nohault'		=> false ),

array( 'prettyname'		=> "GD Library",
	   'extensionname'	=> "gd",
	   'helpurl'		=> "http://www.php.net/manual/en/image.setup.php",
	   'testfor'		=> 'gd',
	   'nohault'		=> true ),
	
	
array( 'prettyname'		=> "Reflection Class",
	   'extensionname'	=> "Reflection",
	   'helpurl'		=> "http://uk2.php.net/manual/en/language.oop5.reflection.php",
	   'testfor'		=> 'Reflection',
	   'nohault'		=> false ),
);