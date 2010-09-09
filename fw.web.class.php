<?php
/**
 * Framework of web
 *
 *
 */
class NyaaFWWeb extends NyaaFW
{
	/**
	 * @param NyaaStore Configuration
	 */
	function __construct( $Conf )
	{
		$this->Conf = $Conf;
		parent::__construct( );
	}

	function init( )
	{
		parent::init( );

		require_once 'fw/handler/form.class.php';
		require_once 'fw/handler/resource.class.php';
		$this->registerHandler(self::WHEN_BEFORE_RUN, array(new NyaaFWHandlerForm(), 'trigger'));
		$this->registerHandler(self::WHEN_BEFORE_RUN, array(new NyaaFWHandlerResource(), 'trigger'));
		$this->registerHandler(self::WHEN_BEFORE_APP_RUN, array($this,'app_run'));
	}

	function app_run( $fw, $App )
	{
		$App->dump( );
	}

}
?>
