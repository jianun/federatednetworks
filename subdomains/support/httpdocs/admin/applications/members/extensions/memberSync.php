<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Forum permissions mappings
 * Last Updated: $Date: 2010-04-26 19:40:44 +0100 (Mon, 26 Apr 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6179 $ 
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
 * @package		IP.Board
 * @subpackage  Members
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6179 $ 
 **/
class membersMemberSync
{
	/**
	 * Registry reference
	 *
	 * @access	public
	 * @var		object
	 */
	public $registry;
	
	/**
	 * CONSTRUCTOR
	 *
	 * @access	public
	 * @return	void
	 **/
	public function __construct()
	{
		$this->registry = ipsRegistry::instance();
	}
	
	/**
	 * This method is run when a member is flagged as a spammer
	 *
	 * @access	public
	 * @param	array 	$member	Array of member data
	 * @return	void
	 **/
	public function onSetAsSpammer( $member )
	{
		/* Load status class */
		if ( ! $this->registry->isClassLoaded( 'memberStatus' ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/status.php', 'memberStatus' );
			$this->registry->setClass( 'memberStatus', new $classToLoad( ipsRegistry::instance() ) );
		}
		
		/* Delete the stuff */
		$this->registry->getClass('memberStatus')->setAuthor( $member );
		$this->registry->getClass('memberStatus')->deleteAllReplies();
		$this->registry->getClass('memberStatus')->deleteAllMemberStatus();
	}
	
	/**
	 * This method is run when a member is un-flagged as a spammer
	 *
	 * @access	public
	 * @param	array 	$member	Array of member data
	 * @return	void
	 **/
	public function onUnSetAsSpammer( $member )
	{

	}
}