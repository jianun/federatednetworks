<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Installer: License Key
 * Last Updated: $LastChangedDate: 2010-07-19 11:06:34 -0400 (Mon, 19 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6673 $
 *
 */


class upgrade_license extends ipsCommand
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
		/* If less than 3, just bounce out as settings tables won't be here, etc */
		if ( IPSSetUp::is300plus() !== TRUE )
		{
			$this->registry->autoLoadNextAction( 'upgrade' );
			return;
		}

		if ( $this->request['do'] == 'check' )
		{
			$lcheck = $this->check();
			if ( $lcheck === TRUE )
			{
				$this->registry->autoLoadNextAction( 'upgrade' );
				return;
			}
		}
		else
		{
			$lcheck = $this->check( TRUE );
			if ( $lcheck === TRUE )
			{
				$this->registry->autoLoadNextAction( 'upgrade' );
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
	private function check( $init=FALSE )
	{
		$this->request['lkey'] = ( $init ) ? ipsRegistry::$settings['ipb_reg_number'] : trim( $this->request['lkey'] );

		if ( !$this->request['lkey'] and !$init )
		{
			return true;
		}
							
		$url = ipsRegistry::$settings['board_url'];
		
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
		$query = new classFileManagement();
		$query->use_sockets = 1;
		$response = $query->getFileContents( "http://licsrv.invisionpower.com/license_check/index.php?api=activateKey&key={$this->request['lkey']}&domain={$url}" );
		$response = json_decode( $response, true );
						
		if( $response['error'] AND ( strtolower( $response['error'] ) != 'that key has already been activated' ) )
		{
			return $response['error'];
		}
		else
		{
			IPSLib::updateSettings( array( array( 'ipb_reg_number' => $this->request['lkey'] ) ) );
			return TRUE;
		}
						
	}
}