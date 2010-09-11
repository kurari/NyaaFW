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

	function doSnippet( $method, $option )
	{
		return call_user_func( array($this,'snip'.ucfirst($method)), $option);
	}

}
?>
