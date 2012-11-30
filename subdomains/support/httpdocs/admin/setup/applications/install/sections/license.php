<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Installer: License Key
 * Last Updated: $LastChangedDate: 2010-06-16 11:21:04 -0400 (Wed, 16 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6541 $
 *
 */


class install_license extends ipsCommand
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
		$lcheck = '';
		if ( $this->request['do'] == 'check' )
		{
			$lcheck = $this->check();
			if ( $lcheck === TRUE )
			{
				$this->registry->autoLoadNextAction( 'db' );
				return;
			}
		}
	
		$this->registry->output->setTitle( "License Key" );
		$this->registry->output->setNextAction( "license&do=check" );
		$this->registry->output->addContent( $this->registry->output->template()->page_license( $lcheck ) );
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Check License Key
	 *
	 * @access	public
	 * @return	bool
	 */
	private function check()
	{
		$this->request['lkey'] = trim( $this->request['lkey'] );
		
		// License key is optional
		if( ! $this->request['lkey'] )
		{
			return true;
		}
		
		$url = IPSSetup::getSavedData( 'install_url' );
		
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
		$query = new classFileManagement();
		$query->use_sockets = 1;
		$response = $query->getFileContents( "http://licsrv.invisionpower.com/license_check/index.php?api=activateKey&key={$this->request['lkey']}&domain={$url}" );
		$response = json_decode( $response, true );
		
		if( $response['error'] )
		{
			return $response['error'];
		}
		else
		{
			IPSSetup::setSavedData( 'lkey', $this->request['lkey'] );
			return TRUE;
		}
						
	}
}