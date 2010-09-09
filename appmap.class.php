<?php
/**
 * Application Mapper
 */

class NyaaFWAppmap extends NyaaObject
{
	private $FW;

	public static function factory( $type, $fw )
	{
		if( $type == "default" )
		{
			$Appmap = new NyaaFWAppmap( $fw );
		}
		return $Appmap;
	}

	function __construct( $fw )
	{
		$this->FW = $fw;
		parent::__construct( );
	}

	function getCurrentAppName( )
	{
		$fw = $this->FW;
		$app = $fw->Request->getOr('app', $fw->Conf->get('app.default') );
		if($app{0} == '#') $app =  $fw->Conf->get('app.map.'.substr($app, 1));
		return $app;
	}

	function createUrl( $name )
	{
		$fw = $this->FW;
		return $fw->Request->get('site.url').'/app/'.$name;
	}

	function getAppInfo( $name )
	{
		if($name{0} == '#') $name =  $fw->Conf->get('name.map.'.substr($name, 1));

		$fw = $this->FW;
		$file = $fw->Conf->get('app.dir').'/'.$name.'.class.php';
		$class = preg_replace('/([^.]+)\.{0,1}/e','ucfirst("\1")', $name ).'App';
		return array($name, $file, $class);
	}
}
?>
