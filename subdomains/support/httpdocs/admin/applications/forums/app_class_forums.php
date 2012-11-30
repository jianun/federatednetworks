<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Forums application initialization
 * Last Updated: $LastChangedDate: 2010-07-10 01:01:06 -0400 (Sat, 10 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		14th May 2003
 * @version		$Rev: 6628 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class app_class_forums
{
	/**#@+
	 * Registry Object Shortcuts
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	/**#@-*/
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry )
	{
		/* Make object */
		$this->registry = $registry;
		$this->DB       = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->cache    = $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
		$this->lang     = $this->registry->getClass('class_localization');
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		
		if ( IN_ACP )
		{
			try
			{
				require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php" );
				require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/admin_forum_functions.php" );
				
				$this->registry->setClass( 'class_forums', new admin_forum_functions( $registry ) );
				$this->registry->getClass('class_forums')->strip_invisible = 0;
			}
			catch( Exception $error )
			{
				IPS_exception_error( $error );
			}
		}
		else
		{
			try
			{
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php", 'class_forums', 'forums' );
				$this->registry->setClass( 'class_forums', new $classToLoad( $registry ) );
				$this->registry->getClass('class_forums')->strip_invisible = 1;
			}
			catch( Exception $error )
			{
				IPS_exception_error( $error );
			}
		}
		
		//---------------------------------------------------
		// Grab and cache the topic now as we need the 'f' attr for
		// the skins...
		//---------------------------------------------------
		
		if ( isset( $_GET['showtopic'] ) AND $_GET['showtopic'] != '' )
		{
			$this->request['t'] = intval( $_GET['showtopic']  );
			
			if ( $this->settings['cpu_watch_update'] AND $this->memberData['member_id'] )
			{
				$this->DB->build( array( 'select' => 't.*',
										 'from'   => array( 'topics' => 't' ),
										 'where'  => 't.tid=' . $this->request['t'],
										 'add_join' => array( array( 'select' => 'w.trid as trackingTopic',
																	 'from'   => array( 'tracker' => 'w' ),
																	 'where'  => 'w.topic_id=t.tid AND w.member_id=' . $this->memberData['member_id'],
																	 'type'   => 'left' ) ) ) );
				$this->DB->execute();
				
				$topic = $this->DB->fetch();
			}
			else
			{
				$topic = $this->DB->buildAndFetch( array( 'select' => '*',
														  'from'   => 'topics',
														  'where'  => "tid=" . $this->request['t'],
												)      );
			}
										
			$this->registry->getClass('class_forums')->topic_cache = $topic;
	   
		    $this->request['f'] =  $topic['forum_id'];
			
			/* Update query location */
			$this->member->sessionClass()->addQueryKey( 'location_2_id', ipsRegistry::$request['f'] );
		}
		
		$this->registry->getClass('class_forums')->forumsInit();
		
		//-----------------------------------------
		// Set up moderators
		//-----------------------------------------
		
		$this->memberData = IPSMember::setUpModerator( $this->memberData );
	}
	
	/**
	* Do some set up after ipsRegistry::init()
	*
	* @access	public
	*/
	public function afterOutputInit()
	{
		if ( isset( $_GET['showtopic'] ) AND $_GET['showtopic'] != '' AND is_array( $this->registry->getClass('class_forums')->topic_cache ) )
		{
			$topic = $this->registry->getClass('class_forums')->topic_cache;
			$topic['title_seo'] = ( $topic['title_seo'] ) ? $topic['title_seo'] : IPSText::makeSeoTitle( $topic['title'] );
			
			/* Check TOPIC permalink... */
			$this->registry->getClass('output')->checkPermalink( $topic['title_seo'] );
			
			/* Add canonical tag */
			$this->registry->getClass('output')->addCanonicalTag( ( $this->request['st'] ) ? 'showtopic=' . $topic['tid'] . '&st=' . $this->request['st'] : 'showtopic=' . $topic['tid'], $topic['title_seo'], 'showtopic' );
			
			/* Store root doc URL */
			$this->registry->getClass('output')->storeRootDocUrl( $this->registry->getClass('output')->buildSEOUrl( 'showtopic=' . $topic['tid'], 'publicNoSession', $topic['title_seo'], 'showtopic' ) );
		}
		else if ( isset( $_GET['showforum'] ) AND $_GET['showforum'] != '' )
		{
			$data             = $this->registry->getClass('class_forums')->forumsFetchData( $_GET['showforum'] );
			$data['name_seo'] = ( $data['name_seo'] ) ? $data['name_seo'] : IPSText::makeSeoTitle( $data['name'] );
			
			/* Check FORUM permalink... */
			$this->registry->getClass('output')->checkPermalink( $data['name_seo'] );
			
			/* Add canonical tag */
			if( $data['id'] )
			{
				$this->registry->getClass('output')->addCanonicalTag( ( $this->request['st'] ) ? 'showforum=' . $data['id'] . '&st=' . $this->request['st'] : 'showforum=' . $data['id'], $data['name_seo'], 'showforum' );
				
				/* Store root doc URL */
				$this->registry->getClass('output')->storeRootDocUrl( $this->registry->getClass('output')->buildSEOUrl( 'showforum=' . $data['id'], 'publicNoSession', $data['name_seo'], 'showforum' ) );
			}
		}
		else if ( isset( $_GET['showannouncement'] ) AND $_GET['showannouncement'] != '' )
		{
			//$announce = $this->DB->buildAndFetch( array( 'select' => 'announce_id, announce_title',
			//										  'from'   => 'announcements',
			//										  'where'  => 'announce_id=' . intval( $_GET['showannouncement'] ) ) );
			$announce	= $this->caches['announcements'][ intval( $_GET['showannouncement'] ) ];
													
			if ( $announce['announce_id'] )
			{
				$_seoTitle	= $announce['announce_seo_title'] ? $announce['announce_seo_title'] : IPSText::makeSeoTitle( $announce['announce_title'] );
				
				$this->registry->getClass('output')->checkPermalink( $_seoTitle );
				
				/* Add canonical tag */
				if( $announce['announce_id'] )
				{
					$this->registry->getClass('output')->addCanonicalTag( 'showannouncement=' . $announce['announce_id'] . ( $_GET['f'] ? '&amp;f=' . intval($_GET['f']) : '&amp;f=0' ), $_seoTitle, 'showannouncement' );
					
					/* Store root doc URL */
					$this->registry->getClass('output')->storeRootDocUrl( $this->registry->getClass('output')->buildSEOUrl( 'showannouncement=' . $announce['announce_id'] . ( $_GET['f'] ? '&amp;f=' . intval($_GET['f']) : '&amp;f=0' ), 'publicNoSession', $_seoTitle, 'showannouncement' ) );
				}
			}
		}
	}
}