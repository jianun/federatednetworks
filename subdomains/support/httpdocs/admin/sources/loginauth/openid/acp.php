<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Login handler abstraction : OpenID method
 * Last Updated: $Date: 2010-06-28 20:04:10 -0400 (Mon, 28 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 6584 $
 *
 */

$config		= array(
					array(
							'title'			=> 'File-Store location',
							'description'	=> "This is the location where the openid filestore should be placed.  Directory must be writable.",
							'key'			=> 'store_path',
							'type'			=> 'string'
						),
					array(
							'title'			=> 'Optional args to pull',
							'description'	=> "Based on OpenID specs, optional args to request.  See <a href='http://openid.net/specs/openid-simple-registration-extension-1_0.html' target='_blank'>OpenID Specs</a> for more information.  If nickname and email are available, account will be fully created with no user intervention required.",
							'key'			=> 'args_opt',
							'type'			=> 'string'
						),
					array(
							'title'			=> 'Required args to pull',
							'description'	=> "Based on OpenID specs, optional args to request.  See <a href='http://openid.net/specs/openid-simple-registration-extension-1_0.html' target='_blank'>OpenID Specs</a> for more information.  If nickname and email are available, account will be fully created with no user intervention required.",
							'key'			=> 'args_req',
							'type'			=> 'string'
						),
					array(
							'title'			=> 'Policy URL',
							'description'	=> 'This is a url to the policy on data you collect (optional)',
							'key'			=> 'openid_policy',
							'type'			=> 'string'
						),
					);