<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Sphinx template file
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5713 $
 * @since		3.0.0
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$appSphinxTemplate	= <<<EOF

############################### --- CORE --- ################################

source core_search_main : ipb_source_config
{
	# Set our forum PID counter
	sql_query_pre	= REPLACE INTO <!--SPHINX_DB_PREFIX-->cache_store VALUES( 'sphinx_core_counter', (SELECT max(id) FROM <!--SPHINX_DB_PREFIX-->faq), '', 0, UNIX_TIMESTAMP() )
	
	# Query posts for the main source
	sql_query		= SELECT f.*, f.id as search_id, \
							 REPLACE( pi.perm_view, '*', 0 ) as perm_view \
					  FROM <!--SPHINX_DB_PREFIX-->faq f \
					  LEFT JOIN <!--SPHINX_DB_PREFIX-->permission_index pi ON ( pi.perm_type_id=1 AND pi.perm_type='help' ) \
					  WHERE f.id <= ( SELECT cs_value FROM <!--SPHINX_DB_PREFIX-->cache_store WHERE cs_key='sphinx_core_counter' )
	
	# Fields	
	sql_attr_uint	= search_id
	
	sql_ranged_throttle	= 0
}

source core_search_delta : core_search_main
{
	# Override the base sql_query_pre
	sql_query_pre	= 
	
	# Query posts for the main source
	sql_query		= SELECT f.*, f.id as search_id, \
							 REPLACE( pi.perm_view, '*', 0 ) as perm_view \
					  FROM <!--SPHINX_DB_PREFIX-->faq f \
					  LEFT JOIN <!--SPHINX_DB_PREFIX-->permission_index pi ON ( pi.perm_type_id=1 AND pi.perm_type='help' ) \
					  WHERE f.id > ( SELECT cs_value FROM <!--SPHINX_DB_PREFIX-->cache_store WHERE cs_key='sphinx_core_counter' )
	
	# Fields	
	sql_attr_uint	= search_id
	
	sql_ranged_throttle	= 0
}

index core_search_main
{
	source			= core_search_main
	path			= <!--SPHINX_BASE_PATH-->/core_search_main
	
	docinfo			= extern
	mlock			= 0
	morphology		= none
	min_word_len	= 2
	charset_type	= sbcs
	html_strip		= 0	
}

index core_search_delta : core_search_main
{
   source			= core_search_delta
   path				= <!--SPHINX_BASE_PATH-->/core_search_delta
}


EOF;
