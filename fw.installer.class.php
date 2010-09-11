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
		$this->Conf = $Conf;

		foreach( $this->Conf->get('app.dir','template.dir','data.dir') as $dir ) {
			if(!is_dir($dir)) {
				mkdir($dir, true);
				chmod($dir, 0777);
			}
		}
		$db = $this->Conf->get('data.dir').'/system.db';

		if(!file_exists($db)){
			$this->db = NyaaDB::factory('sqlite://localhost/'.$db);
			$this->db->query('CREATE TABLE packages (name varchar(128) primary key);');
		}else{
			$this->db = NyaaDB::factory('sqlite://localhost/'.$db);
		}
		$sth = $this->db->query('SELECT name FROM packages;');
		foreach($sth as $v){
			$this->enables[] = $v['name'];
		}
		parent::__construct( );
	}

	function update( $name )
	{
		$this->disable( $name );
		$this->enable( $name );
	}


	function disable( $name )
	{
		if( !in_array( $name, $this->enables ) ){
			$this->warning('package %s is alrady disabled', $name);
			return false;
		}

		$sth = $this->db->prepare('DELETE FROM packages WHERE name=:name');
		$sth->bindParam('name',$name);
		$sth->execute();

		$key = array_search($name, $this->enables);
		unset($this->enables[$key]);

		$dir    = $this->Conf->get('package.dir').'/'.$name;
		$appdir = $this->Conf->get('app.dir');
		$tpldir = $this->Conf->get('template.dir');
		$info   = NyaaConf::load( $dir.'/info.conf' );

		foreach( $info->get('app') as $k=>$file ){
			$to = $appdir.'/'.$name.'.'.$k.'.class.php';
			unlink( $to );
		}

		foreach( $info->get('template') as $k=>$file ){
			$to = $tpldir.'/'.$name.'.'.$k.'.html';
			unlink( $to );
		}
	}

	function enable( $name )
	{
		if( in_array( $name, $this->enables ) ){
			$this->warning('package %s is alrady enabled', $name);
			return false;
		}

		$sth = $this->db->prepare('INSERT INTO packages (name) VALUES (:name)');
		$sth->bindParam('name',$name);
		$sth->execute();

		$dir    = $this->Conf->get('package.dir').'/'.$name;
		$appdir = $this->Conf->get('app.dir');
		$tpldir = $this->Conf->get('template.dir');
		$info   = NyaaConf::load( $dir.'/info.conf' );

		$info->dump( );

		foreach( $info->get('app') as $k=>$file ){
			$from = $dir.'/'.$file;
			$to = $appdir.'/'.$name.'.'.$k.'.class.php';
			symlink( $from, $to );
		}

		foreach( $info->get('template') as $k=>$file ){
			$from = $dir.'/'.$file;
			$to = $tpldir.'/'.$name.'.'.$k.'.html';
			symlink( $from, $to );
		}
	}

}
?>
