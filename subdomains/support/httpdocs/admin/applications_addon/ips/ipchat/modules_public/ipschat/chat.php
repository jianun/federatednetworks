<?php

/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Chat services
 * Last Updated: $Date: 2009-09-16 21:34:08 -0400 (Wed, 16 Sep 2009) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Chat
 * @link		http://www.invisionpower.com
 * @since		Fir 12th Aug 2005
 * @version		$Revision: 5123 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class public_ipchat_ipschat_chat extends ipsCommand
{
	/**
	 * Master chat server URL
	 *
	 * @var	string
	 */
	const MASTERSERVER	= "http://chatservice.ipsdns.com/";
	
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
		// Load lang file
		//-----------------------------------------
		
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_chat' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_editors' ), 'core' );

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
			$this->registry->output->showError( 'no_chat_account_number', 'CHAT-01' );
		}
		
		$this->settings['ipschat_account_key'] = trim( $this->settings['ipschat_account_key'] );

		//-----------------------------------------
		// Can we access?
		//-----------------------------------------

		$access_groups = explode( ",", $this->settings['ipschat_group_access'] );
		
		$my_groups = array( $this->memberData['member_group_id'] );
		
		if( $this->memberData['mgroup_others'] )
		{
			$my_groups = array_merge( $my_groups, explode( ",", IPSText::cleanPermString( $this->memberData['mgroup_others'] ) ) );
		}
		
		$access_allowed = false;
		
		foreach( $my_groups as $group_id )
		{
			if( in_array( $group_id, $access_groups ) )
			{
				$access_allowed = 1;
				break;
			}
		}
		
		if( !$access_allowed OR !$this->memberData['member_id'] )
		{
			$this->registry->output->showError( 'no_chat_access', 'CHAT-02' );
		}
		
		//-----------------------------------------
		// Are we banned?
		//-----------------------------------------
		
		if( $this->memberData['chat_banned'] )
		{
			$this->registry->output->showError( 'no_chat_access', 'CHAT-03' );
		}
    	
		//-----------------------------------------
		// Is it offline?
		//-----------------------------------------
		
		if( !$this->settings['ipschat_online'] )
		{
			$offline_groups = explode( ",", $this->settings['ipschat_offline_groups'] );
			
			$access_allowed = false;
			
			foreach( $my_groups as $group_id )
			{
				if( in_array( $group_id, $offline_groups ) )
				{
					$access_allowed = 1;
					break;
				}
			}
			
			if( !$access_allowed )
			{
				$this->_isOffline();
			}
    	}
    	
    	//-----------------------------------------
    	// Chat only online during certain hours of the day
    	//-----------------------------------------
    	
    	if( $this->settings['ipschat_online_start'] AND $this->settings['ipschat_online_end'] )
    	{
    		$_currentHour	= gmstrftime('%H') + ( $this->settings['time_offset'] - ( $this->memberData['dst_in_use'] ? -1 : 0 ) );

			//-----------------------------------------
			// Rollback if we cross midnight
			//-----------------------------------------
			
			if( $_currentHour < 0 )
			{
				$_currentHour	= 24 + $_currentHour;
			}

			//-----------------------------------------
			// Open 12:00 - 15:00
			//-----------------------------------------
			
			if( $this->settings['ipschat_online_end'] > $this->settings['ipschat_online_start'] )
			{
	    		if( !($_currentHour >= $this->settings['ipschat_online_start']) OR !($_currentHour < $this->settings['ipschat_online_end']) )
	    		{
	    			$this->_isOffline();
	    		}
    		}
    		
    		//-----------------------------------------
    		// Open 22:00 - 02:00
    		//-----------------------------------------
    		
    		else
    		{
    			$_open	= true;
    			
    			//-----------------------------------------
    			// Only check if we are not past the start time yet
    			// i.e. if at 23:00 we know it's open
    			//-----------------------------------------
    			
    			if( !($_currentHour >= $this->settings['ipschat_online_start']) )
    			{
    				//-----------------------------------------
    				// Now, if we're past end time, it's closed
    				// since we already know it's not past start time
    				//-----------------------------------------
    				
    				if( $_currentHour >= $this->settings['ipschat_online_end'] )
    				{
    					$_open	= false;
    				}
    				
    			}
    			
	    		if( !$_open )
	    		{
	    			$this->_isOffline();
	    		}
    		}
    	}
    	
    	//-----------------------------------------
    	// Did we request to leave chat?
    	//-----------------------------------------
    	
    	if( $this->request['do'] == 'leave' )
    	{
    		$this->_leaveChat();
    	}
    	
		//-----------------------------------------
		// Moderator permissions
		//-----------------------------------------
		
		$permissions	= 0;
		$private		= 0;
		
		if( $this->settings['ipschat_mods'] )
		{
			$mod_groups = explode( ",", $this->settings['ipschat_mods'] );

			foreach( $my_groups as $group_id )
			{
				if( in_array( $group_id, $mod_groups ) )
				{
					$permissions = 1;
					break;
				}
			}
    	}
    	
		if( $this->settings['ipschat_private'] )
		{
			$mod_groups = explode( ",", $this->settings['ipschat_private'] );

			foreach( $my_groups as $group_id )
			{
				if( in_array( $group_id, $mod_groups ) )
				{
					$private = 1;
					break;
				}
			}
    	}
    	
    	//-----------------------------------------
    	// Agree to rules?
    	//-----------------------------------------
    	
		if( $this->settings['ipschat_enable_rules'] )
		{
			if( !$_POST['agree'] )
			{
				$this->agreeToTerms();
				return;
			}
		}

		//-----------------------------------------
		// Get going!
		//-----------------------------------------
		
		$userId			= 0;
		$userName		= $this->cleanUsername( $this->memberData['members_display_name'] );
		$roomId			= 0;
		$accessKey		= '';
		
		$result		= $this->_callServer( self::MASTERSERVER . "gateway31.php?api_key={$this->settings['ipschat_account_key']}&user={$userName}&level={$permissions}" );
		$results	= explode( ',', $result );
		
		if( $results[0] == 0 )
		{
			$this->registry->output->showError( $this->lang->words['connect_gw_error_' . $results[1] ] ? $this->lang->words['connect_gw_error_' . $results[1] ] : $this->lang->words['connect_error'], 'CSTART-' . intval($results[1]) );
		}

		$roomId		= intval($results[3]);
		$serverHost	= $results[1];
		$serverPath	= $results[2];
	
		$result		= $this->_callServer( "http://{$serverHost}{$serverPath}/join31.php?api_key={$this->settings['ipschat_account_key']}&user={$userName}&level={$permissions}&room={$roomId}&forumid={$this->memberData['member_id']}&forumgroup={$this->memberData['member_group_id']}" );
		$results	= explode( ',', $result );

		if( $results[0] == 0 )
		{
			$this->registry->output->showError( $this->lang->words['connect_error_' . $results[1] ] ? $this->lang->words['connect_error_' . $results[1] ] : $this->lang->words['connect_error'], 'CJOIN-' . intval($results[1]) );
		}
		
		$userId		= $results[1];
		$accessKey	= $results[2];

    	//-----------------------------------------
    	// Set the options...
    	//-----------------------------------------
    	
    	$options	= array(
    						'roomId'			=> $roomId,
    						'userId'			=> $userId,
    						'accessKey'			=> $accessKey,
    						'serverHost'		=> $serverHost,
    						'serverPath'		=> $serverPath,
    						'ourUrl'			=> urlencode($this->settings['board_url']),
    						'moderator'			=> $permissions,
    						'private'			=> $private,
    						);

		$this->cache->setCache( 'chatserver', array( 'host' => $serverHost, 'path' => $serverPath ), array( 'donow' => 1, 'array' => 1 ) );

		//-----------------------------------------
		// Get online list
		//-----------------------------------------
		
		$online		= array();
		$memberIds	= array();
		$result		= $this->_callServer( "http://{$serverHost}{$serverPath}/list.php?room={$roomId}&user={$userId}&access_key={$accessKey}" );
		
		if( $result )
		{
			$results	= explode( "~~||~~", $result );
			
			if( $results[0] == 1 )
			{
				foreach( $results as $k => $v )
				{
					if( $k == 0 )
					{
						continue;
					}
					
					$_thisRecord	= explode( ',', $v );
					
					if( !$_thisRecord[0] )
					{
						continue;
					}

					$online[]		= array(
											'user_id'	=> $_thisRecord[0],
											'user_name'	=> str_replace( '~~#~~', ',', $_thisRecord[1] ),
											'forum_id'	=> $_thisRecord[2]
											);

					$memberIds[ $_thisRecord[2] ]	= intval($_thisRecord[2]);
				}
			}
		}
		
		$members	= IPSMember::load( $memberIds );
		$chatters	= array();
		
		foreach( $online as $_online )
		{
			$_online['member']	= IPSMember::buildDisplayData( $members[ $_online['forum_id'] ] );
			
			$_online['member']['prefix']	= str_replace( '"', '__DBQ__', $_online['member']['prefix'] );
			$_online['member']['suffix']	= str_replace( '"', '__DBQ__', $_online['member']['suffix'] );
			
			$chatters[ strtolower($members[ $_online['forum_id'] ]['members_display_name']) ]	= $_online;
		}
		
		ksort($chatters);
		
		//-----------------------------------------
		// Emoticons
		//-----------------------------------------
		
		$emoticons	= $this->_getEmoticons();

		//-----------------------------------------
		// Output
		//-----------------------------------------
		
		$this->output .= $this->registry->getClass('output')->getTemplate('ipchat')->chatRoom( $options, $chatters, $emoticons );
		
		//-----------------------------------------
		// Put us in "chatting"
		//-----------------------------------------
		
		$tmp_cache = $this->cache->getCache('chatting');
		$new_cache = array();

		if ( is_array( $tmp_cache ) and count( $tmp_cache ) )
		{
			foreach( $tmp_cache as $id => $data )
			{
				//-----------------------------------------
				// Not hit in 2 mins?
				//-----------------------------------------
				
				if ( $data['updated'] < ( time() - 120 ) )
				{
					continue;
				}
				
				//-----------------------------------------
				// This us?
				//-----------------------------------------
				
				if ( $id == $this->memberData['member_id'] )
				{
					$data['updated']	= time();
				}
				
				//-----------------------------------------
				// No user id?
				//-----------------------------------------
				
				if( !$data['userid'] )
				{
					continue;
				}
				
				//-----------------------------------------
				// Not online according to server?
				//-----------------------------------------
				
				$_isOnlineServer	= false;
				
				foreach( $online as $_serverOnline )
				{
					if( $_serverOnline['forum_id'] == $id )
					{
						$_isOnlineServer	= true;
					}
				}
				
				if( !$_isOnlineServer )
				{
					continue;
				}
				
				$new_cache[ $id ] = $data;
			}
		}
		
		if( !$new_cache[ $this->memberData['member_id'] ] )
		{
			$new_cache[ $this->memberData['member_id'] ]	= array( 'updated' => time(), 'userid' => $userId, 'username' => $this->memberData['members_display_name'] );
		}
		
		//-----------------------------------------
		// Add any from server that are missing
		//-----------------------------------------
		
		foreach( $online as $_serverOnline )
		{
			if( !array_key_exists( $_serverOnline['forum_id'], $new_cache ) )
			{
				$new_cache[ $_serverOnline['forum_id'] ]	= array(
																	'updated'	=> time(),
																	'userid'	=> $_serverOnline['user_id'],
																	'username'	=> $_serverOnline['user_name'],
																	);
			}
		}

		//-----------------------------------------
		// Update cache
		//-----------------------------------------
														  
		$this->cache->setCache( 'chatting', $new_cache, array( 'donow' => 1, 'array' => 1 ) );
		
		//-----------------------------------------
		// Add our JS files
		//-----------------------------------------

		$this->registry->output->addToDocumentHead( 'javascript', $this->settings['public_dir'] . 'sounds/soundmanager2-nodebug-jsmin.js' );
		$this->registry->output->addToDocumentHead( 'raw', "<script type='text/javascript'>soundManager.url = '{$this->settings['public_dir']}/sounds/';soundManager.debugMode=false;</script>" );
		$this->registry->output->addToDocumentHead( 'javascript', $this->settings['public_dir'] . 'js/ips.chat.js' );
		
		$_ie	= <<<EOF
<!--[if lte IE 7]>
	<link rel="stylesheet" type="text/css" title='ChatIE7' media="screen" href="{$this->settings['public_dir']}style_css/ipchat_ie.css" />
<![endif]-->
EOF;
		$this->registry->output->addToDocumentHead( 'raw', $_ie );
		
		//-----------------------------------------
		// Show chat..
		//-----------------------------------------

		$this->registry->output->addNavigation( $this->lang->words['chat_title'], '' );
		$this->registry->output->setTitle( $this->lang->words['chat_title'] . ' - ' . $this->settings['board_name'] );
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Clean username for chat
	 *
	 * @access	private
	 * @param	string		Username
	 * @return	string		HTML
	 */
	protected function cleanUsername( $username )
	{
		$username		= str_replace( "\r", '', $username );
		$username		= str_replace( "\n", '__N__', $username );
		$username		= str_replace( ",", '__C__', $username );
		$username		= str_replace( "=", '__E__', $username );
		$username		= str_replace( "+", '__PS__', $username );
		$username		= str_replace( "&", '__A__', $username );
		$username		= str_replace( "%", '__P__', $username );
		$username		= urlencode($username);
		
		return $username;
	}
	
	/**
	 * Get the emoticons for popup list
	 *
	 * @access	private
	 * @return	string		HTML
	 */
	private function _getEmoticons()
	{
		/* INIT */
 		$smilie_id        = 0;

		/* Query the emoticons */
 		$this->DB->build( array( 'select' => 'typed, image', 'from' => 'emoticons', 'where' => "emo_set='".$this->registry->output->skin['set_emo_dir']."'" ) );
		$this->DB->execute();
		
		/* Loop through and build output array */
		$rows = array();
		
		if( $this->DB->getTotalRows() )
		{
			while( $r = $this->DB->fetch() )
			{
				$smilie_id++;
				
				if( strstr( $r['typed'], "&quot;" ) )
				{
					$in_delim  = "'";
					$out_delim = '"';
				}
				else
				{
					$in_delim  = '"';
					$out_delim = "'";
				}
				
				$rows[] = array(
								'code'       => stripslashes( $r['typed'] ),
								'image'      => stripslashes( $r['image'] ),
								'in'         => $in_delim,
								'out'        => $out_delim,
								'smilie_id'	 =>	$smilie_id							
							);					
			}
		}
		
		return $this->registry->getClass('output')->getTemplate('legends')->emoticonPopUpList( 'message', $rows );
	}

	/**
	 * Show a screen requiring users to agree to terms before continuing
	 *
	 * @access	private
	 * @return	void
	 */
	private function agreeToTerms()
	{
		IPSText::getTextClass('bbcode')->parse_bbcode		= 1;
		IPSText::getTextClass('bbcode')->parse_html			= 1;
		IPSText::getTextClass('bbcode')->parse_nl2br		= 1;
		IPSText::getTextClass('bbcode')->parse_emoticons	= 1;
		IPSText::getTextClass('bbcode')->parsing_section	= 'global';
		
		$this->settings['ipschat_rules']	= IPSText::getTextClass('bbcode')->preDbParse( $this->settings['ipschat_rules'] );
		$this->settings['ipschat_rules']	= IPSText::getTextClass('bbcode')->preDisplayParse( $this->settings['ipschat_rules'] );

		$this->registry->output->addNavigation( $this->lang->words['chat_title'], '' );
		$this->registry->output->setTitle( $this->lang->words['chat_title'] . ' - ' . $this->settings['board_name'] );
		$this->registry->output->addContent( $this->registry->getClass('output')->getTemplate('ipchat')->chatRules( $this->settings['ipschat_rules'] ) );
		$this->registry->output->sendOutput();
	}
		
	/**
	 * Show an offline message
	 *
	 * @access	private
	 * @return	void
	 */
	private function _isOffline()
	{
		IPSText::getTextClass('bbcode')->parse_bbcode		= 1;
		IPSText::getTextClass('bbcode')->parse_html			= 1;
		IPSText::getTextClass('bbcode')->parse_nl2br		= 1;
		IPSText::getTextClass('bbcode')->parse_emoticons	= 1;
		IPSText::getTextClass('bbcode')->parsing_section	= 'global';
		
		$this->settings['ipschat_offline_msg']	= IPSText::getTextClass('bbcode')->preDbParse( $this->settings['ipschat_offline_msg'] );
		$this->settings['ipschat_offline_msg']	= IPSText::getTextClass('bbcode')->preDisplayParse( $this->settings['ipschat_offline_msg'] );

		$this->registry->output->showError( $this->settings['ipschat_offline_msg'], 'CHAT-04' );
	}
	
	/**
	 * Leave chat
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _leaveChat()
	{
		//-----------------------------------------
		// Get server info from cache
		//-----------------------------------------

		$this->request['secure_key'] = $this->request['secure_key'] ? $this->request['secure_key'] : $this->request['md5check'];

		if( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'usercp_forums_bad_key', '10CHAT99' );
		}

		$cache	= $this->cache->getCache('chatserver');
		
		if( $cache['host'] )
		{
			//-----------------------------------------
			// Tell server we've left
			//-----------------------------------------
			
			$result		= $this->_callServer( "http://{$cache['host']}{$cache['path']}/leave.php?room={$this->request['room']}&user={$this->request['user']}&access_key={$this->request['access_key']}" );
		}
		
		//-----------------------------------------
		// Remove us from "chatting"
		//-----------------------------------------
		
		$tmp_cache = $this->cache->getCache('chatting');
		$new_cache = array();

		if ( is_array( $tmp_cache ) and count( $tmp_cache ) )
		{
			foreach( $tmp_cache as $id => $data )
			{
				//-----------------------------------------
				// Not hit in 2 mins?
				//-----------------------------------------
				
				if ( $data['updated'] < ( time() - 120 ) )
				{
					continue;
				}
				
				//-----------------------------------------
				// This us?
				//-----------------------------------------
				
				if ( $id == $this->memberData['member_id'] )
				{
					continue;
				}
				
				//-----------------------------------------
				// No user id?
				//-----------------------------------------
				
				if( !$data['userid'] )
				{
					continue;
				}
				
				$new_cache[ $id ] = $data;
			}
		}
											  
		$this->cache->setCache( 'chatting', $new_cache, array( 'donow' => 1, 'array' => 1 ) );
		
		//-----------------------------------------
		// And redirect
		//-----------------------------------------
		
		$this->registry->output->redirectScreen( $this->lang->words['you_left_room'] , $this->settings['base_url'] );
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