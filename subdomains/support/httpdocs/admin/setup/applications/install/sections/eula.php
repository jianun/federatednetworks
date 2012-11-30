<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Installer: EULA file
 * Last Updated: $LastChangedDate: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5713 $
 *
 */


class install_eula extends ipsCommand
{	
	/**
	 * Execute selected method
	 *
	 * @access	public
	 * @param	object		Registry object
	 * @return	void
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		/* Simply return the EULA page */
		$this->registry->output->setTitle( "EULA" );
		$this->registry->output->addContent( $this->registry->output->template()->page_eula() );
		$this->registry->output->sendOutput();
	}
}