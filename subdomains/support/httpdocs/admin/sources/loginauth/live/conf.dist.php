<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Loginauth configuration file
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 5713 $
 */
 
$LOGIN_CONF = array();

/**
* Key file location
* This is the location where the Live Application Key XML file is located
*/
$LOGIN_CONF['key_file_location'] = IPS_ROOT_PATH . 'sources/loginauth/live/Application-Key.xml';

/**
* Login URL
* The location to send the user to for login purposes.  You should not need to changet his
*/
$LOGIN_CONF['login_url'] = 'http://login.live.com/wlogin.srf';