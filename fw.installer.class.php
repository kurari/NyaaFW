<?php
/**
 * Framework of Installer
 *
 *
 */
require_once 'db/db.class.php';

class NyaaFWInstaller extends NyaaFW
{
	public $enables = array( );

	/**
	 * @param NyaaStore Configuration
	 */
	function __construct( $Conf )
	{
		$this->info = array( );
		$this->appdir  = $Conf->get('app.dir');
		$this->tpldir  = $Conf->get('template.dir');
		$this->pkgdir  = $Conf->get('package.dir');
		$this->rootdir = $Conf->get('root.dir');

		foreach($Conf->get('app.dir','template.dir') as $dir){
			if(!is_dir($dir)){
				mkdir($dir);
				chmod($dir, 0777);
			}
		}
		parent::__construct( );
	}

	function save( )
	{
		$file = $this->rootdir . "/var/installed";
		file_put_contents($file, serialize($this->info));
	}

	function install( $name )
	{
		$dir    = $this->pkgdir."/".$name;
		$this->doInstall( $dir, $name );
	}

	function doInstall( $dir, $name )
	{
		$conf   = $dir.'/package.conf';
		$Conf   = NyaaConf::load($conf);

		$info =& $this->info;
		
		foreach($Conf->getOr('app',array()) as $k=>$v)
		{
			if($k == "_root") {
				$key = $key2 = $name;
				$key2 = $name.".class.php";
			}else{
				$key = "$name.$k";
				$key2 = "$name.$v";
			}
			$from = $dir.'/'.$v;
			$to = $this->appdir ."/$key2";
			if(file_exists($to)) unlink($to);
			symlink( $from, $to);
			$info['app'][$key]['file'] = $this->appdir."/$key2";
			$info['app'][$key]['class'] = preg_replace('/([^.]+)[.]{0,1}/e','ucfirst("\1")', $key).'App';
			$info['app'][$key]['info'] = $Conf->getOr("info.app.$k", "");
		}

		foreach($Conf->getOr('alias',array()) as $k=>$v)
		{
			if($v == "_root"){
				$key = "$name.$k";
				$to  = $name;
			}else{
				$key = "$name.$k";
				$to  = "$name.$v";
			}
			$info['app'][$key] = $info['app'][$to];
			$info['app'][$key]['info'] = "Redirect Of $to";
		}

		foreach($Conf->getOr('template',array()) as $k=>$v)
		{
			$key  = "$name.$k";
			$from = $dir.'/'.$v;
			$to   = $this->tpldir ."/$name.$v";
			if(file_exists($to)) unlink($to);
			symlink( $from, $to);
			$info['template'][$key]['file'] = $this->tpldir."/$name.$v";
			$info['template'][$key]['info'] = $Conf->getOr("info.template.$k", "");
		}

		foreach($Conf->getOr('package',array()) as $k=>$v)
		{
			$this->doInstall( $dir.'/'.$v, "$name.$k" );
		}
	}

}
?>
