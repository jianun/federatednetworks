<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.0.4
 * Chat default section
 * Last Updated: $LastChangedDate: 2009-07-28 20:26:37 -0400 (Tue, 28 Jul 2009) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Chat
 * @link		http://www.invisionpower.com
 * @since		17 February 2003
 * @version		$Revision: 4106 $
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}


class admin_ipchat_logs_logs extends ipsCommand
{
	/**
	 * Master chat server URL
	 *
	 * @var	string
	 */
	const MASTERSERVER	= "http://chatservice.ipsdns.com/";
	
	/**
	 * How many logs per page
	 *
	 * @access	public
	 * @var		int
	 */
	public $perPage			= 100;

	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		//-----------------------------------------
		// Load HTML
		//-----------------------------------------
		
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_chat' );
		
		$this->registry->class_localization->loadLanguageFile( array( 'admin_chat', 'public_chat' ) );
		
		//-----------------------------------------
		// Set up stuff
		//-----------------------------------------
		
		$this->form_code	= $this->html->form_code	= 'module=logs&amp;section=logs';
		$this->form_code_js	= $this->html->form_code_js	= 'module=logs&section=logs';
		
		switch($this->request['do'])
		{
			case 'refresh':
				$this->refreshLogs();
			break;
			
			default:
			case 'view':
				$this->_viewLogs();
			break;
		}
		
		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------
		
		$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
		$this->registry->getClass('output')->sendOutput();
	}
	
	/**
	 * Refresh the chat logs
	 *
	 * @access	public
	 * @param	bool		Return, instead of redirect
	 * @return	mixed		void (if return is false), or an error string
	 */
	public function refreshLogs( $return=false )
	{
		//-----------------------------------------
		// IPB 3.1
		//-----------------------------------------
		
		if( $this->settings['ipb_reg_number'] )
		{
			$this->settings['ipschat_account_key']	= $this->settings['ipb_reg_number'];
		}
		
		//-----------------------------------------
		// Check that we have the key
		//-----------------------------------------
		
		if ( ! $this->settings['ipschat_account_key'] )
		{
			if( $return )
			{
				return $this->lang->words['connect_error'];
			}
			
			$this->registry->output->showError( 'no_chat_account_number', 'CHAT-0A1' );
		}
		
		$max		= $this->DB->buildAndFetch( array( 'select' => 'MAX(log_time) AS logged_time', 'from' => 'chat_log_archive' ) );
		$result		= $this->_callServer( self::MASTERSERVER . "get_logs.php?api_key={$this->settings['ipschat_account_key']}&timestamp=" . $max['logged_time'] );
		$results	= explode( ',', $result );
		
		if( $results[0] == 0 )
		{
			if( $return )
			{
				return $this->lang->words['connect_gw_error_' . $results[1] ] ? $this->lang->words['connect_gw_error_' . $results[1] ] : $this->lang->words['connect_error'];
			}

			$this->registry->output->showError( $this->lang->words['connect_gw_error_' . $results[1] ] ? $this->lang->words['connect_gw_error_' . $results[1] ] : $this->lang->words['connect_error'], 'CSTART-' . intval($results[1]) );
		}
		
		foreach( $results as $k => $v )
		{
			if( $k < 1 )
			{
				continue;
			}
			
			$_thisResult	= explode( "~~||~~", $v );
			
			if( !$_thisResult[1] )
			{
				continue;
			}

			$_insert		= array(
									'log_room_id'		=> $_thisResult[1],
									'log_time'			=> $_thisResult[2],
									'log_code'			=> $_thisResult[3],
									'log_user'			=> $_thisResult[4],
									'log_message'		=> $_thisResult[5],
									'log_extra'			=> $_thisResult[6],
									);

			$this->DB->insert( "chat_log_archive", $_insert );
		}
		
		if( $return )
		{
			return $this->lang->words['chatlogs_refreshed'];
		}
		else
		{
			$this->registry->output->global_message	= $this->lang->words['chatlogs_refreshed'];
			$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'] . $this->form_code );
		}
	}
	
	/**
	 * View the chat logs
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _viewLogs()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$st			= intval($this->request['st']);
		$filters	= array();
		$_urlBits	= array(
							'visibility=' . ( $this->request['visibility'] ? $this->request['visibility'] : 'public' ),
							);

		if( $this->request['visibility'] )
		{
			switch( $this->request['visibility'] )
			{
				case 'public':
					$filters[]	= "( log_extra NOT LIKE 'private=%' OR log_extra IS NULL )";
				break;
				
				case 'private':
					$filters[]	= "log_extra LIKE 'private=%'";
				break;
			}
		}
		else
		{
			$this->request['visibility']	= 'public';
			$filters[]	= "( log_extra NOT LIKE 'private=%' OR log_extra IS NULL )";
		}
		
		if( $this->request['keyword'] )
		{
			$filters[]	= "( log_message LIKE '%{$this->request['keyword']}%' OR log_user LIKE '%{$this->request['keyword']}%')";
			$_urlBits[]	= "keyword=" . $this->request['keyword'];
		}
		
		if( $this->request['date_from'] AND $this->request['date_to'] )
		{
			$_from	= intval( @strtotime( $this->request['date_from'] ) );
			$_to	= intval( @strtotime( $this->request['date_to'] ) );
			
			$filters[]	= "log_time BETWEEN {$_from} AND {$_to}";
			$_urlBits[]	= "date_from=" . urlencode($this->request['date_from']);
			$_urlBits[]	= "date_to=" . urlencode($this->request['date_to']);
		}
		else if( $this->request['date_from'] )
		{
			$_from	= intval( @strtotime( $this->request['date_from'] ) );
			
			$filters[]	= "log_time > {$_from}";
			$_urlBits[]	= "date_from=" . urlencode($this->request['date_from']);
		}
		else if( $this->request['date_to'] )
		{
			$_to	= intval( @strtotime( $this->request['date_to'] ) );
			
			$filters[]	= "log_time < {$_to}";
			$_urlBits[]	= "date_to=" . urlencode($this->request['date_to']);
		}

		//-----------------------------------------
		// Pagination
		//-----------------------------------------
		
		$count	= $this->DB->buildAndFetch( array( 'select' => 'count(*) as total', 'from' => 'chat_log_archive', 'where' => implode( " AND ", $filters ) ) );
		
		$pages	= $this->registry->output->generatePagination( array( 
																	'totalItems'		=> $count['total'],
																	'itemsPerPage'		=> $this->perPage,
																	'currentStartValue'	=> $st,
																	'baseUrl'			=> $this->settings['base_url'] . $this->form_code . '&amp;' . implode( '&amp;', $_urlBits ),
											 				)	);

		//-----------------------------------------
		// Get parser ready...
		//-----------------------------------------
		
		$_newBbcodes	= array();
		$_oldBbcodes	= $this->cache->getCache('bbcode');
		
		foreach( $_oldBbcodes as $_key => $_code )
		{
			if( in_array( $_code['bbcode_tag'], array( 'b', 'i', 'u', 'url' ) ) )
			{
				$_newBbcodes[ $_key ]	= $_code;
			}
		}
		
		$this->cache->updateCacheWithoutSaving( 'bbcode', $_newBbcodes );
		
		IPSText::getTextClass('bbcode')->parse_bbcode		= 1;
		IPSText::getTextClass('bbcode')->parse_html			= 0;
		IPSText::getTextClass('bbcode')->parse_nl2br		= 0;
		IPSText::getTextClass('bbcode')->parse_emoticons	= 1;
		IPSText::getTextClass('bbcode')->parsing_section	= 'global';
		IPSText::getTextClass('bbcode')->bypass_badwords	= true;
		
		//-----------------------------------------
		// Get actual logs
		//-----------------------------------------
		
		$this->DB->build( array( 'select' => '*', 'from' => 'chat_log_archive', 'where' => implode( " AND ", $filters ), 'order' => 'log_time DESC', 'limit' => array( $st, $this->perPage ) ) );
		$outer	= $this->DB->execute();
		
		$_logs	= array();
		
		while( $r = $this->DB->fetch($outer) )
		{
			//-----------------------------------------
			// Process data here
			//-----------------------------------------
			
			$r['_log_date']		= $this->registry->getClass('class_localization')->getDate( $r['log_time'], 'SHORT' );
			$r['_classname']	= 'acp-row-off';
			
			$r['_log_message']	= $r['log_message'];
			$r['_log_message']	= $this->_cleanMessage( $r['_log_message'] );
			$r['log_user']		= $this->_cleanMessage( $r['log_user'] );
			

			if( $r['log_code'] == 2 )
			{
				$_extra	= explode( '_', $r['log_extra'] );

				if( $_extra[0] == 1 )
				{
					$r['_log_message']	= sprintf( $this->lang->words['chatlog__hasentered'], $r['log_user'] );
				}
				else
				{
					$r['_log_message']	= sprintf( $this->lang->words['chatlog__hasleft'], $r['log_user'] );
				}
				
				$r['_classname']	= 'chat-notice';
			}
			else if( $r['log_code'] == 5 )
			{
				$r['_log_message']	= $this->lang->words['kicked_pre'] . $r['_log_message'];
				$r['_classname']	= 'chat-moderator';
			}
			
			if( strpos( $r['_log_message'], '/me' ) === 0 )
			{
				$r['_log_message']	= substr( $r['_log_message'], 4 );
				$r['_classname']	= 'chat-me';
			}
			
			if( strpos( $r['log_extra'], 'private=' ) === 0 )
			{
				$_user	= substr( $r['log_extra'], strrpos( $r['log_extra'], '=' ) + 1 );

				$_user	= IPSMember::load( $_user );
				
				$r['_log_message']	= sprintf( $this->lang->words['private_pre'], $_user['members_display_name'] ) . $r['_log_message'];
				$r['_classname']	= 'chat-private';
			}
			
			$r['_log_message']	= IPSText::getTextClass('bbcode')->preDbParse( $r['_log_message'] );
			$r['_log_message']	= IPSText::getTextClass('bbcode')->preDisplayParse( $r['_log_message'] );
			
			$_logs[]	= $r;
		}
		
		$this->cache->updateCacheWithoutSaving( 'bbcode', $_oldBbcodes );
		
		//-----------------------------------------
		// Output
		//-----------------------------------------
		
		$this->registry->output->html .= $this->html->logs( $pages, $_logs );
	}
	
	/**
	 * Clean "special" characters
	 *
	 * @access	protected
	 * @param	string		String to clean
	 * @return	string		Cleaned string
	 */
	protected function _cleanMessage( $string )
	{
		$string	= str_replace( "__N__"  , "\n", $string ); 
		$string	= str_replace( "__C__"  , ",", $string ); 
		$string	= str_replace( "__E__"  , "=", $string ); 
		$string	= str_replace( "__PS__" , "+", $string ); 
		$string	= str_replace( "__A__"  , "&", $string ); 
		$string	= str_replace( "__P__"  , "%", $string );
		
		return $string;
	}

	/**
	 * Get response from chat servers.  This is mostly copied from
	 * classFileManagement.php, however I needed HTTP 1.1 support, so
	 * I had to copy it and update.
	 *
	 * @access	protected
	 * @param	string		URL to call
	 * @return	string		Response from server
	 */
	protected function _callServer( $url )
	{
		//-----------------------------------------
		// Try CURL first
		//-----------------------------------------
		
		if ( function_exists( 'curl_init' ) AND function_exists("curl_exec") )
		{
			$ch = curl_init( $url );
			
			curl_setopt( $ch, CURLOPT_HEADER		, 0);
			curl_setopt( $ch, CURLOPT_TIMEOUT		, 5 );
			curl_setopt( $ch, CURLOPT_POST			, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
			curl_setopt( $ch, CURLOPT_FAILONERROR	, 1 ); 
			curl_setopt( $ch, CURLOPT_MAXREDIRS		, 2 );
			curl_setopt( $ch, CURLOPT_HTTP_VERSION	, CURL_HTTP_VERSION_1_1 );
			
			/**
			 * Cannot set this when safe_mode or open_basedir is enabled
			 * @link http://forums.invisionpower.com/index.php?autocom=tracker&showissue=11334
			 */
			if( !ini_get('open_basedir') AND !ini_get('safe_mode') )
			{
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 ); 
			}

			$data = curl_exec($ch);
			curl_close($ch);
			
			return trim($data);
		}

		//-----------------------------------------
		// Fall back to sockets
		//-----------------------------------------

		$data				= null;
		$fsocket_timeout	= 20;
		
		//-----------------------------------------
		// Get details
		//-----------------------------------------

		$url_parts = @parse_url($url);
		
		if ( ! $url_parts['host'] )
		{
			return '0';
		}

		$host = $url_parts['host'];
	 	$port = ( isset($url_parts['port']) ) ? $url_parts['port'] : ( $url_parts['scheme'] == 'https' ? 443 : 80 );

	 	if ( !empty( $url_parts["path"] ) )
		{
			$path = $url_parts["path"];
		}
		else
		{
			$path = "/";
		}
 
		if ( !empty( $url_parts["query"] ) )
		{
			$path .= "?" . $url_parts["query"];
		}
	 	
	 	//-----------------------------------------
	 	// Establish connection
	 	//-----------------------------------------

	 	if ( ! $fp = @fsockopen( $host, $port, $errno, $errstr, $fsocket_timeout ) )
	 	{
			return '0';
		}
		else
		{
			if ( ! fputs( $fp, "GET {$path} HTTP/1.1\r\nHost: {$host}\r\nConnection: Keep-Alive\r\n\r\n" ) )
			{
				return '0';
			}
		}

		@stream_set_timeout( $fp, $fsocket_timeout );
		
		$status = @stream_get_meta_data($fp);
		
		while( ! feof($fp) && ! $status['timed_out'] )		
		{
		  $data .= fgets( $fp, 8192 );
		  $status = stream_get_meta_data($fp);
		}
		
		fclose ($fp);

		//-----------------------------------------
		// Clean the result and return
		//-----------------------------------------

		// HTTP/1.1 ### ABCD
		$this->http_status_code = substr( $data, 9, 3 );
		$this->http_status_text = substr( $data, 13, ( strpos( $data, "\r\n" ) - 13 ) );

		$_chunked	= false;
		
		if( preg_match( "/Transfer\-Encoding:\s*chunked/i", $data ) )
		{
			$_chunked	= true;
		}
		
		$tmp	= split( "\r\n\r\n", $data, 2 );
		$data	= trim($tmp[1]);

		if( $_chunked )
		{
			$lines	= explode( "\n", $data );
			array_pop($lines);
			array_shift($lines);
			$data	= implode( "\n", $lines );
		}

 		return trim($data);
	}
}