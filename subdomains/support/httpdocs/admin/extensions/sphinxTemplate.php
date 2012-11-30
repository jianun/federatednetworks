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

$sphinxTemplate	= <<<EOF

#############################################################################
## data source definition
#############################################################################

source ipb_source_config
{
	#setup
	type					= mysql
	sql_host				= <!--SPHINX_CONF_HOST-->
	sql_user				= <!--SPHINX_CONF_USER-->
	sql_pass				= <!--SPHINX_CONF_PASS-->
	sql_db					= <!--SPHINX_CONF_DATABASE-->
	sql_port				= <!--SPHINX_CONF_PORT-->
}

<!--SPHINX_CONTENT-->

#############################################################################
## indexer settings
#############################################################################

indexer
{
	mem_limit			= 256M
}

#############################################################################
## searchd settings
#############################################################################

searchd
{
	address				= 127.0.0.1
	port				= <!--SPHINX_PORT-->
	log					= <!--SPHINX_BASE_PATH-->/log/searchd.log
	query_log			= <!--SPHINX_BASE_PATH-->/log/query.log
	read_timeout		= 5
	max_children		= 30
	pid_file			= <!--SPHINX_BASE_PATH-->/log/searchd.pid
	max_matches			= 1000
	seamless_rotate		= 0
	preopen_indexes		= 0
	unlink_old			= 1
}

# --eof--

EOF;
