<?php
/**
 * Framework of web
 *
 *
 */
require_once 'fw/templater.class.php';

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
		$this->registerHandler(self::WHEN_BEFORE_RUN, array($this,'resource'));
		$this->registerHandler(self::WHEN_BEFORE_APP_RUN, array($this,'app_start'));
		$this->registerHandler(self::WHEN_AFTER_APP_RUN, array($this,'app_end'));
	}

	function resource( $fw )
	{
		if($fw->Request->getOr('path_info.1','') == 'resource'){
			$dir = $fw->Conf->resource[$fw->Request->path_info[2]];
			$file =  $fw->Request->slicePath($fw->Request->path_info[2]);
			echo file_get_contents( $dir.'/'.$file );
			return false;
		}
		return true;
	}

	function app_start( $fw, $App )
	{
		ob_start();
	}

	function app_end( $fw, $App )
	{
		$contents = ob_get_clean();
		$Tpl = $this->getTemplater( );
		$Tpl->set('conf',$this->Conf->get());
		$Tpl->set('mainContents', $contents);
		echo $Tpl->fetch($this->Conf->rootDir.'/template/theme.html');
	}

	function getTemplater( ){
		$Tpl = new NyaaFWTemplater( $this );
		$Tpl->set('conf',$this->Conf->get());
		return $Tpl;
	}

	function snippet( $opt, $template, $Templater )
	{
		$app     = $opt['app'];
		$pos     = strrpos($opt['app'], '.');
		$method  = substr($opt['app'], $pos + 1);
		$appName = substr($opt['app'], 0, $pos);
		$App     = $this->appFactory($appName);
		$result = $App->doSnippet( $method, $opt );
		if(!is_array($result)){
			echo $result;
		}
		$org = $Templater->get();
		$Templater->set($result);
		echo $Templater->fetch('string://'.$template);
		$Templater->set($org);
	}

}
?>
