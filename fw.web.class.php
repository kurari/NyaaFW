<?php
/**
 * Framework of web
 *
 *
 */
require_once 'fw/templater.class.php';

class NyaaFWWeb extends NyaaFW
{
	public $message = array();
	public $results = array();

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
		$this->registerHandler(self::WHEN_BEFORE_RUN, array($this,'form'));
		$this->registerHandler(self::WHEN_BEFORE_RUN, array($this,'resource'));
		$this->registerHandler(self::WHEN_BEFORE_APP_RUN, array($this,'app_start'));
		$this->registerHandler(self::WHEN_AFTER_APP_RUN, array($this,'app_end'));
	}

	function form( $fw)
	{
		if(false !== $app = $fw->Request->getOr('__FORM_ID__', false))
		{
			$pos     = strrpos($app, '.');
			$method  = substr($app, $pos + 1);
			$appName = substr($app, 0, $pos);
			$App     = $this->appFactory($appName);
			$result  = call_user_func(array($App,'apply'.ucfirst($method)), $fw->Request);
			if(empty($result)) {
				$result = $this->resultError( $fw->Request );
			}
			$this->setResult( $app, $result );
		}
	}
	function resultError( $Req, $errors = array())
	{
		$res = new NyaaStore( );
		$res->set(array('status'=>'error', 'request'=>$Req->get(), 'errors'=>$errors));
		return $res;
	}
	function setResult($key, $result)
	{
		$this->results[$key] = $result;
	}
	function getResult($key)
	{
		return isset($this->results[$key]) ? $this->results[$key]: new NyaaStore();
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
		$text = $Tpl->fetch($this->Conf->rootDir.'/template/theme.html');
		echo preg_replace('/LANG\{(.*?)}/e', 'isset($this->message["\1"]) ? $this->message["\1"]: "\1"', $text);
		//echo $text;
	}

	function getTemplater( ){
		$Tpl = new NyaaFWTemplater( $this );
		$Tpl->set('conf',$this->Conf->get());
		return $Tpl;
	}

	function loadMessage( $file )
	{
		$CH = NyaaCache::current( );
		if(self::RELEASE === false || false === $msg = $CH->get($file))
		{
			$label = false;
			$buf = array();
			$arr = array();
			foreach(file($file) as $line){
				$line = trim($line);
				if($line == "."){
					$arr[$label] = implode("\n",$buf);
					$buf = array();
					$label = false;
					continue;
				}
				if($label == false && $line[strlen($line)-1] == ":"){
					$label = substr($line, 0, strlen($line)-1);
					continue;
				}
				if($label !== false)
				{
					$buf[] = $line;
				}
			}
			$msg = $arr;
			$CH->set($file, $msg);
		}
		$this->message = array_merge( $this->message, $msg); 
	}

	function redirect( $to, $option )
	{
		$url = $this->Conf->siteUrl.'/index.php/app/'.$to;
		$App = $this->appFactory('system.redirect');
		$store = new NyaaStoreRequest( );
		$store->set($option);
		$store->set('to', $url);
		$App->setRequest( $store );
		echo $App->run( );
		die();
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
