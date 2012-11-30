<?php

/**
 * Invision Power Services
 * IP.Chat
 * Remove user from chatting cache when they log out
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 4429 $ 
 **/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * Member Synchronization extensions
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage  Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 4429 $ 
 **/
class ipchatMemberSync
{
	/**#@+
	 * Registry references
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $registry;
	protected $DB;
	protected $cache;
	/**#@-*/
	
	/**
	 * CONSTRUCTOR
	 *
	 * @access	public
	 * @return	void
	 **/
	public function __construct()
	{
		$this->registry = ipsRegistry::instance();
		$this->DB		= $this->registry->DB();
		$this->cache    = $this->registry->cache();
	}
	
	/**
	 * This method is run when a user logs out
	 *
	 * @access	public
	 * @param	array 	$member	Array of member data
	 * @return	void
	 **/
	public function onLogOut( $member )
	{
		$chatters		= $this->cache->getCache('chatting');
		$newChatters	= array();
		$_update		= false;
		
		if( count($chatters) AND is_array($chatters) )
		{
			foreach( $chatters as $k => $v )
			{
				if( $k == $member['member_id'] )
				{
					$_update	= true;
					continue;
				}
				
				$newChatters[ $k ]	= $v;
			}
		}
		
		if( $_update )
		{
			$this->cache->setCache( 'chatting', $newChatters );
		}
	}
}