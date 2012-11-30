<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Notifications class for reported content
 * Last Updated: $LastChangedDate: 2010-07-18 18:38:38 -0400 (Sun, 18 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @author		Based on original "Report Center" by Luke Scott
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6669 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class reportNotifications
{
	/**#@+
	 * Registry objects
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
	 * Messenger object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $messenger;
	
	/**
	 * Data for the members
	 *
	 * @access	public
	 * @var		array
	 */	
	public $my_data;

	/**
	 * Data for the reported content
	 *
	 * @access	public
	 * @var		array
	 */	
	public $my_report_data;
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object		Registry object
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Make object
		//-----------------------------------------
		
		$this->registry 	= $registry;
		$this->DB	    	= $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->member   	= $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache		= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
		$this->lang			= $this->registry->getClass('class_localization');

		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'members' ) . '/sources/classes/messaging/messengerFunctions.php', 'messengerFunctions', 'members' );
		$this->messenger	= new $classToLoad( $this->registry );
	}
	
	/**
	 * Initialize library
	 *
	 * @access	public
	 * @param	array 		Member data
	 * @param	array 		Reported content	
	 * @return	void
	 */
	public function initNotify( $data, $report_data )
	{
		$this->my_data			= $data;
		$this->my_report_data	= $report_data;
	}
	
	/**
	 * Send the notifications
	 *
	 * @access	public
	 * @return	void
	 */
	public function sendNotifications()
	{
		//-----------------------------------------
		// Notifications library
		//-----------------------------------------
		
		$classToLoad		= IPSLib::loadLibrary( IPS_ROOT_PATH . '/sources/classes/member/notifications.php', 'notifications' );
		$notifyLibrary		= new $classToLoad( $this->registry );
		
		$_memberIds		= array();
		
		foreach( $this->my_data as $_data )
		{
			$_memberIds[]	= $_data['member_id'];
		}

		$_memberData	= IPSMember::load( $_memberIds );
		
		foreach( $this->my_data as $user )
		{
			//-----------------------------------------
			// Don't send notification to self
			//-----------------------------------------
			
			if( $user['member_id'] == $this->memberData['member_id'] )
			{
				continue;
			}

			$user	= array_merge( $user, $_memberData[ $user['member_id'] ] );

			IPSText::getTextClass( 'email' )->getTemplate( "report_emailpm", $user['language'] );
			
			IPSText::getTextClass( 'email' )->buildMessage( array(
																'MOD_NAME'	=> $user['members_display_name'],
																'USERNAME'	=> $this->memberData['members_display_name'],
																'LINK'		=> $this->registry->getClass('reportLibrary')->processUrl( $this->my_report_data['SAVED_URL'], $this->my_report_data['SEOTITLE'], $this->my_report_data['TEMPLATE'] ),
																'REPORTLINK'=> $this->settings['base_url'] . 'app=core&module=reports&do=show_report&rid=' . $this->my_report_data['REPORT_INDEX'],
																'REPORT'	=> $this->my_report_data['REPORT'],
																	)
															);

			$_subject	= sprintf(
									$this->lang->words['subject_report'],
									$this->registry->output->buildSEOUrl( 'showuser=' . $this->memberData['member_id'], 'public', $this->memberData['members_seo_name'], 'showuser' ), 
									$this->memberData['members_display_name'],
									$this->settings['base_url'] . 'app=core&module=reports&do=show_report&rid=' . $this->my_report_data['REPORT_INDEX'],
									$this->registry->getClass('reportLibrary')->processUrl( $this->my_report_data['SAVED_URL'], $this->my_report_data['SEOTITLE'], $this->my_report_data['TEMPLATE'] )
									);
			$notifyLibrary->setMember( $user );
			$notifyLibrary->setFrom( $this->memberData );
			$notifyLibrary->setNotificationKey( 'report_center' );
			$notifyLibrary->setNotificationUrl( $this->settings['base_url'] . 'app=core&module=reports&do=show_report&rid=' . $this->my_report_data['REPORT_INDEX'] );
			$notifyLibrary->setNotificationText( IPSText::getTextClass('email')->message );
			$notifyLibrary->setNotificationTitle( $_subject );
			
			try
			{
				$notifyLibrary->sendNotification();
			}
			catch( Exception $e ){}
		}

		$this->_buildRSSFeed( $this->my_data, $this->my_report_data );
	}

	/**
	 * Build a private RSS feed for the member to monitor reports
	 *
	 * @access	private
	 * @return	void
	 */
	private function _buildRSSFeed( $data=array(), $report_data )
	{
		$ids = array();
		
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_reports' ), 'core' );

		if( is_array($data) AND count($data) )
		{
			foreach( $data as $user )
			{
				$ids[] = $user['member_id'];
			}
		}
		
		if( count( $ids ) == 0 )
		{
			return;
		}
		
		require_once( IPS_KERNEL_PATH . 'classRss.php' );
		
		$status = array();
		
		$this->DB->build( array( 'select' 	=> 'status, is_new, is_complete', 
										 'from'		=> 'rc_status', 
										 'where'	=> "is_new=1 OR is_complete=1",
								) 		);
		$this->DB->execute();

		while( $row = $this->DB->fetch() )
		{
			if( $row['is_new'] == 1 )
			{
				$status['new'] = $row['status'];
			}
			elseif( $row['is_complete'] == 1 )
			{
				$status['complete'] = $row['status'];
			}
		}

		//-----------------------------------------
		// Now, we loop over each of the member ids
		//-----------------------------------------
		
		foreach( $ids as $id )
		{
			//-----------------------------------------
			// Clear out for new RSS doc and add channel
			//-----------------------------------------
			
			$rss			=  new classRss();
			$channel_id = $rss->createNewChannel( array( 'title'			=> $this->lang->words['rss_feed_title'],
															'link'			=> $this->settings['board_url'],
															'description'	=> $this->lang->words['reports_rss_desc'],
															'pubDate'		=> $rss->formatDate( time() )
												)		);

			//-----------------------------------------
			// Now we need to find all open reports for
			// this member
			//-----------------------------------------
			
			$this->DB->build( array(
									'select'	=> 'i.*',
									'from'		=> array( 'rc_reports_index' => 'i' ),
									'where'		=> 's.is_active=1',
									'add_join'	=> array(
														array(
															'from'		=> array( 'rc_status' => 's' ),
															'where'		=> 's.status=i.status',
															'type'		=> 'left',
															),
														array(
															'select'	=> 'c.my_class, c.mod_group_perm, c.app',
															'from'		=> array( 'rc_classes' => 'c' ),
															'where'		=> 'c.com_id=i.rc_class',
															'type'		=> 'left',
															),
														)
							)		);
			$outer = $this->DB->execute();
			
			while( $r = $this->DB->fetch($outer) )
			{
				/* Deleted report plugin, skip */
				if( $r['my_class'] == '' )
				{
					continue;
				}

				//-----------------------------------------
				// Fix stuff....this is hackish :(
				//-----------------------------------------
				
				if( $r['my_class'] == 'post' )
				{
					$r['FORUM_ID']	= $r['exdat1'];
				}
				
				//-----------------------------------------
				// Found all open reports, can we access?
				//-----------------------------------------
				
				require_once( IPSLib::getAppdir( $r['app'] ) . '/extensions/reportPlugins/' . $r['my_class'] . '.php' );
				$class 	= $r['my_class'] . '_plugin';
				$object	= new $class( $this->registry );
				
				$notify	= $object->getNotificationList( IPSText::cleanPermString( $r['mod_group_perm'] ), $r );
				$pass	= false;
				
				if( is_array($notify['RSS']) AND count($notify['RSS']) )
				{
					foreach( $notify['RSS'] as $memberAccount )
					{
						if( $memberAccount['mem_id'] == $id )
						{
							$pass = true;
							break;
						}
					}
				}
				
				if( $pass )
				{
					$url = $this->registry->getClass('reportLibrary')->processUrl( str_replace( '&amp;', '&', $r['url'] ) );
					
					$rss->addItemToChannel( $channel_id, array( 'title'			=> $url,
																'link'			=> $url,
																'description'	=> $r['title'],
																'content'		=> $r['title'],
																'pubDate'		=> $rss->formatDate( $r['date_updated'] )
										)					);
				}
			}

			$rss->createRssDocument();
	
			$this->DB->update( 'rc_modpref', array( 'rss_cache' => $rss->rss_document ), "mem_id=" . $id );
		}
	}
}