<?php
/**
 * Application
 * ----
 */

class NyaaFWApp extends NyaaStore
{
	protected $FW;

	function __construct( $fw )
	{
		$this->FW = $fw;
	}

	function getTemplater( )
	{
		return $this->FW->getTemplater( );
	}

	function init( )
	{

	}

	function run( )
	{

	}

}
?>
