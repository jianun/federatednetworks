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


class install_done extends ipsCommand
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
		$installLocked = FALSE;
		
		/* Lock the page */
		if ( @file_put_contents( DOC_IPS_ROOT_PATH . 'cache/installer_lock.php', 'Just out of interest, what did you expect to see here?' ) )
		{
			$installLocked = TRUE;
		}
		
		/* Clean conf global */
		IPSInstall::cleanConfGlobal();
		
		/* Simply return the EULA page */
		$this->registry->output->setTitle( "Complete!" );
		$this->registry->output->setHideButton( TRUE );
		$this->registry->output->addContent( $this->registry->output->template()->page_installComplete( $installLocked ) );
		$this->registry->output->sendOutput( FALSE );
	}
}