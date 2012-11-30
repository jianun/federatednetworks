<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Loginauth configuration file
 * Last Updated: $Date: 2010-06-30 21:01:53 -0400 (Wed, 30 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 6593 $
 */
 
$LOGIN_CONF = array();

/**
* File-Store location
* This is the location where the openid filestore should be placed
*/
$LOGIN_CONF['store_path'] = DOC_IPS_ROOT_PATH . 'cache/openid';


/**
* Option args to pull
* Based on OpenID specs, optional args to request
* http://www.openidenabled.com/openid/simple-registration-extension
*/

$LOGIN_CONF['args_opt']	= 'nickname,dob,email';//,gender';	= removing gender until core is fixed

/**
* Required args to pull
* Based on OpenID specs, required args to request
* http://www.openidenabled.com/openid/simple-registration-extension
*/

$LOGIN_CONF['args_req']	= '';

/**
* Policy URL
* This is a url to the policy on data you collect (optional)
* 
*/

$LOGIN_CONF['openid_policy']	= '';