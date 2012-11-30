<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Print Topic
 * Last Updated: $Date: 2010-06-10 06:36:42 -0400 (Thu, 10 Jun 2010) $
 * </pre>
 *
 * @author		$Author: danc $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 6502 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_forums_forums_printtopic extends ipsCommand
{
	/**
	 * Temporary output
	 *
	 * @access	private
	 * @var		string
	 */
	private $output = "";
	
	/**
	 * Forum data
	 *
	 * @access	private
	 * @var		array
	 */
	private $forum	= array();
	
	/**
	 * Topic data
	 *
	 * @access	private
	 * @var		array
	 */
	private $topic	= array();

	private $attach_pids;
	
	/**
	 * Main execution function
	 *
	 * @access	public
	 * @param	object	Registry Object
	 * @return	void
	 * @todo 	Need error codes
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$_moderator = $this->memberData['forumsModeratorData'];
		
		//-----------------------------------------
		// Compile the language file
		//-----------------------------------------
		
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_printpage' ) );
		
		//-----------------------------------------
		// Check the input
		//-----------------------------------------
		
		$this->request['t'] =  intval($this->request['t'] );
		$this->request['f'] =  intval($this->request['f'] );
		
		if ( ! $this->request['t'] or ! $this->request['f'] )
		{
			$this->registry->getClass('output')->showError( 'missing_files', 0, null, null, 404 );
		}
		
		//-----------------------------------------
		// Get the forum info based on the
		// forum ID, get the category name, ID,
		// and get the topic details
		//-----------------------------------------
		
		$this->topic = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'topics', 'where' => "tid=" . $this->request['t'] ) );
		$this->forum = $this->registry->getClass('class_forums')->forum_by_id[ $this->topic['forum_id'] ];
							
		//-----------------------------------------
		// Error out if we can not find the forum
		//-----------------------------------------
		
		if ( ! $this->forum['id'] )
		{
			$this->registry->getClass('output')->showError( 'missing_files', 0, null, null, 404 );
		}
		
		//-----------------------------------------
		// Error out if we can not find the topic
		//-----------------------------------------
		
		if ( ! $this->topic['tid'] )
		{
			$this->registry->getClass('output')->showError( 'missing_files', 0, null, null, 404 );
		}
		
		//-----------------------------------------
		// Check viewing permissions, private forums,
		// password forums, etc
		//-----------------------------------------
		
		if ( ( ! $this->topic['pinned'] ) and ( ! $this->memberData['g_other_topics'] ) )
		{
			$this->registry->getClass('output')->showError( 'no_view_topic', 0, null, null, 403 );
		}
		
		//-----------------------------------------
		// Check access
		//-----------------------------------------
		
		$this->registry->getClass('class_forums')->forumsCheckAccess( $this->forum['id'], 1, 'topic' );
		
		if ( ! $this->topic['approved'] AND !( $this->memberData['g_is_supmod'] OR $_moderator[ $this->topic['forum_id'] ]['topic_q'] ) )
		{
			$this->registry->getClass('output')->showError( 'no_view_topic', 0, null, null, 403 );
		}
		
		//-----------------------------------------
		// Main logic engine
		//-----------------------------------------
		
		if ($this->request['client'] == 'choose')
		{
			$this->page_title = $this->topic['title'];
		
			$this->nav = array( array( $this->forum['name'], "showforum={$this->forum['id']}" ),
								 array( $this->topic['title'], "showtopic={$this->topic['tid']}" )
								);
							   
							   
			$this->output = $this->registry->getClass('output')->getTemplate('printpage')->choose_form($this->forum['id'], $this->topic['tid'], $this->topic['title']);

			$this->registry->getClass('output')->setTitle( $this->page_title . ' - ' . ipsRegistry::$settings['board_name'] );
			$this->registry->getClass('output')->addContent( $this->output );
			
			if( is_array( $this->nav ) AND count( $this->nav ) )
			{
				foreach( $this->nav as $_id => $_nav )
				{
					$this->registry->getClass('output')->addNavigation( $_nav[0], $_nav[1] );
				}
			}

			$this->registry->getClass('output')->sendOutput();
			
			exit();
		}
		else
		{
			$header = 'text/html';
			$ext	= '.html';
			
			switch ($this->request['client'])
			{
				case 'printer':
					$header = 'text/html';
					$ext	= '.html';
					break;
				case 'html':
					$header = 'unknown/unknown';
					$ext	= '.html';
					break;
				default:
					$header = 'application/msword';
					$ext	= '.doc';
			}
		}
		
		$title = substr( str_replace( " ", "_" , preg_replace( "/&(lt|gt|quot|#124|#036|#33|#39);/", "", $this->topic['title'] ) ), 0, 100);
		
		@header( "Content-type: {$header};charset=" . IPS_DOC_CHAR_SET );
		
		if ( $this->request['client'] != 'printer' )
		{
			@header("Content-Disposition: attachment; filename=$title".$ext);
		}
		
		print $this->getPosts();
		
		exit;
	}
	
	/**
	 * Gets the posts to be printed/downloaded
	 *
	 * @access	protected
	 * @return	string
	 **/
	protected function getPosts()
	{
		//-----------------------------------------
		// Render the page top
		//-----------------------------------------

		$posts_html = $this->registry->getClass('output')->getTemplate('printpage')->pp_header( $this->forum['name'], $this->topic['title'], $this->topic['starter_name'] , $this->forum['id'], $this->topic['tid'] );

		$max_posts	 = 300;
		$attach_pids = array();
		
		$this->DB->build( array( 
								'select'   => 'p.*',
								'from'	   => array( 'posts' => 'p' ),
								'where'	   => "p.topic_id={$this->topic['tid']} and p.queued=0",
								'order'	   => 'p.pid',
								'limit'	   => array( 0, $max_posts ),
								'add_join' => array(
													array(
															'select' => 'm.members_display_name',
															'from'	 => array( 'members' => 'm' ),
															'where'	 => 'm.member_id=p.author_id',
															'type'	 => 'left'
														)
													)
						)	);
		$this->DB->execute();
		
		//-----------------------------------------	   
		// Loop through to pick out the correct member IDs.
		// and push the post info into an array - maybe in the future
		// we can add page spans, or maybe save to a PDF file?
		//-----------------------------------------
		
		$the_posts		= array();
		$mem_ids		= array();
		$member_array	= array();
		$cached_members = array();
		$pids			= array();
		
		while ( $i = $this->DB->fetch() )
		{
			$the_posts[ $i['pid' ]] = $i;
			$this->attach_pids[]	= $i['pid'];
			
			if ( $i['author_id'] )
			{
				$mem_ids[ $i['author_id'] ] = $i['author_id'];
			}
		}
		
		$the_posts = $this->_parseAttachments( $the_posts, $this->attach_pids );
		
		//-----------------------------------------
		// Get the member profiles needed for this topic
		//-----------------------------------------
		
		if ( count( $mem_ids ) )
		{
			$this->DB->build( array( 
									'select'   => 'm.*',
									'from'	   => array( 'members' => 'm' ),
									'where'	   => 'm.member_id IN ('.implode( ',', $mem_ids ).')',
									'add_join' => array(
														array( 
																'select' => 'g.*',
																'from'	 => array( 'groups' => 'g' ),
																'where'	 => 'g.g_id=m.member_group_id',
																'type'	 => 'left' 
															) 
														) 
							)	);
			
			$this->DB->execute();
		
			while( $m = $this->DB->fetch() )
			{
				$member_array[ $m['member_id'] ] = $m;
			}
		}
		
		//-----------------------------------------
		// Format and print out the topic list
		//-----------------------------------------

		foreach( $the_posts as $row )
		{
			$poster = array();
			
			//-----------------------------------------
			// Get the member info. We parse the data and cache it.
			// It's likely that the same member posts several times in
			// one page, so it's not efficient to keep parsing the same
			// data
			//-----------------------------------------
			
			if( $row['author_id'] != 0 )
			{
				//-----------------------------------------
				// Is it in the hash?
				//-----------------------------------------
				
				if ( isset($cached_members[ $row['author_id'] ]) )
				{
					//-----------------------------------------
					// Ok, it's already cached, read from it
					//-----------------------------------------
					
					$poster = $cached_members[ $row['author_id'] ];
					$row['name_css'] = 'normalname';
				}
				else
				{
					//-----------------------------------------
					// Ok, it's NOT in the cache, is it a member thats
					// not been deleted?
					//-----------------------------------------
					
					if ($member_array[ $row['author_id'] ])
					{
						$row['name_css'] = 'normalname';
						$poster = $member_array[ $row['author_id'] ];
						
						//-----------------------------------------
						// Add it to the cached list
						//-----------------------------------------
						
						$cached_members[ $row['author_id'] ] = $poster;
					}
					else
					{
						//-----------------------------------------
						// It's probably a deleted member, so treat them as a guest
						//-----------------------------------------
						
						$poster = IPSMember::setUpGuest( $row['author_name'] );
						$row['name_css'] = 'unreg';
					}
				}
			}
			else
			{
				//-----------------------------------------
				// It's definately a guest...
				//-----------------------------------------
				
				$poster = IPSMember::setUpGuest( $row['author_name'] );
				$row['name_css'] = 'unreg';
			}

			//-----------------------------------------
			
			$row['post'] = preg_replace( "/<!--EDIT\|(.+?)\|(.+?)-->/", "", $row['post'] );
			
			//-----------------------------------------
		
			$row['post_date']	= $this->registry->getClass( 'class_localization')->getDate( $row['post_date'], 'LONG', 1 );
			
			//-----------------------------------------
			// Quoted attachments?
			//-----------------------------------------
			
			$attach_pids[ $row['pid'] ] = $row['pid'];

			$row['post']	= $this->parseMessage( $row['post'], $row );

			//-----------------------------------------
			// Parse HTML tag on the fly
			//-----------------------------------------
			
			$posts_html .= $this->registry->getClass('output')->getTemplate('printpage')->pp_postentry( $poster, $row );
		}
		
		if ( count( $attach_pids ) )
		{
			if ( ! is_object( $this->class_attach ) )
			{
				//-----------------------------------------
				// Grab render attach class
				//-----------------------------------------

				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'core' ) . '/sources/classes/attach/class_attach.php', 'class_attach' );
				$this->class_attach		   =  new $classToLoad( $this->registry );
				$this->class_attach->type  = 'post';
				$this->class_attach->init();
			}

			$posts_html = $this->class_attach->renderAttachments( $posts_html, $attach_pids );
			
			$posts_html = $posts_html[0]['html'];
		}
		
		//-----------------------------------------
		// Print the footer
		//-----------------------------------------
		
		$posts_html .= $this->registry->getClass('output')->getTemplate('printpage')->pp_end();
		
		//-----------------------------------------
		// Macros
		//-----------------------------------------
		
		$posts_html = $this->registry->getClass('output')->replaceMacros( $posts_html );
		
		//-----------------------------------------
		// CSS
		//-----------------------------------------
		
		$this->registry->getClass('output')->skin['_usecsscache'] = 0;
		
		return $posts_html;
	}
	
	/**
	* Parse attachments
	*
	* @access	public
	* @param	array	Array of post data
	* @return	string	HTML parsed by attachment class
	*/
	public function _parseAttachments( $postData )
	{
		//-----------------------------------------
		// INIT. Yes it is
		//-----------------------------------------
		
		$postHTML = array();
		
		//-----------------------------------------
		// Separate out post content
		//-----------------------------------------
		
		foreach( $postData as $id => $post )
		{
			$postHTML[ $id ] = $post['post'];
		}
		
		//-----------------------------------------
		// ATTACHMENTS!!!
		//-----------------------------------------
		
		if ( $this->topic['topic_hasattach'] )
		{
			if ( ! is_object( $this->class_attach ) )
			{
				//-----------------------------------------
				// Grab render attach class
				//-----------------------------------------

				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'core' ) . '/sources/classes/attach/class_attach.php', 'class_attach' );
				$this->class_attach		   =  new $classToLoad( $this->registry );
			}
			
			//-----------------------------------------
			// Not got permission to view downloads?
			//-----------------------------------------
			
			if ( $this->registry->permissions->check( 'download', $this->registry->class_forums->forum_by_id[ $this->topic['forum_id'] ] ) === FALSE )
			{
				$this->settings['show_img_upload'] =  0 ;
			}
			
			//-----------------------------------------
			// Continue...
			//-----------------------------------------
			
			$this->class_attach->type  = 'post';
			$this->class_attach->init();
			
			# attach_pids is generated in the func_topic_xxxxx files
			$attachHTML = $this->class_attach->renderAttachments( $postHTML, $this->attach_pids );
		}

		/* Now parse back in the rendered posts */
		if( is_array($attachHTML) AND count($attachHTML) )
		{
			foreach( $attachHTML as $id => $data )
			{
				/* Get rid of any lingering attachment tags */
				if ( stristr( $data['html'], "[attachment=" ) )
				{
					$data['html'] = IPSText::stripAttachTag( $data['html'] );
				}
				
				$postData[ $id ]['post']		   = $data['html'];
				$postData[ $id ]['attachmentHtml'] = $data['attachmentHtml'];
			}
		}

		return $postData;
	}
	
	/**
	 * Parses Posts
	 *
	 * @access	protected
	 * @param	string	$message	Text
	 * @param	array	$row		Formatting params
	 * @return	string
	 **/
	protected function parseMessage( $message="", $row=array() )
	{
		IPSText::getTextClass( 'bbcode' )->parse_smilies			= $row['use_emo'];
		IPSText::getTextClass( 'bbcode' )->parse_html				= ( $this->forum['use_html'] and $this->caches['group_cache'][ $row['member_group_id'] ]['g_dohtml'] and $row['post_htmlstate'] ) ? 1 : 0;
		IPSText::getTextClass( 'bbcode' )->parse_nl2br				= $row['post_htmlstate'] == 2 ? 1 : 0;
		IPSText::getTextClass( 'bbcode' )->parse_bbcode				= $this->forum['use_ibc'];
		IPSText::getTextClass( 'bbcode' )->parsing_section			= 'topics';
		IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $row['member_group_id'];
		IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $row['mgroup_others'];

		return IPSText::getTextClass( 'bbcode' )->preDisplayParse( $message );
	}
}
