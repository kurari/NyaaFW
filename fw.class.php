<?php
/**
 * Framework 
 * ---
 *
 */
require_once 'conf/conf.class.php';
require_once 'store/store.class.php';
require_once 'store/request.class.php';
require_once 'store/session.class.php';
require_once 'store/cookie.class.php';
require_once 'fw/appmap.class.php';
require_once 'fw/app.class.php';
require_once 'fw/handler.class.php';

class NyaaFW extends NyaaStore
{
	const WHEN_BEFORE_INIT    = 1;
	const WHEN_AFTER_INIT     = 2;
	const WHEN_BEFORE_RUN     = 3;
	const WHEN_AFTER_RUN      = 4;
	const WHEN_BEFORE_APP_RUN = 5;
	const WHEN_AFTER_APP_RUN  = 6;

	const RELEASE = false;

	public $Conf;
	public $Session;
	public $Request;
	public $Cookie;

	public $handlers;

	/**
	 * factory
	 *
	 * @param string filepath
	 * @param array option {typt,}
	 */
	public static function factory( $file, $option )
	{
		$CH = NyaaCache::current( );
		if( self::RELEASE === false || false === $Conf = $CH->get($file, $option) ){
			$Conf = NyaaConf::load($file, $option);
			$CH->set($file, $Conf);
		}

		$type = $Conf->getOr('fw.type','web');
		$file = dirname(__FILE__)."/fw.$type.class.php";
		$class = 'NyaaFW'.ucfirst($type);
		require_once $file;
		$fw = new $class( $Conf );

		return $fw;
	}

	function __construct( )
	{
		parent::__construct( );
		$this->handlers[self::WHEN_BEFORE_RUN]     = array();
		$this->handlers[self::WHEN_AFTER_RUN]      = array();
		$this->handlers[self::WHEN_BEFORE_APP_RUN] = array();
		$this->handlers[self::WHEN_AFTER_APP_RUN]  = array();
	}

	function appFactory( $name )
	{
		$info = $this->AppInfo['app'][$name];
		require_once $info['file'];
		$App = new $info['class']( $this );
		$App->set('my.url', $this->Conf->siteUrl."/app/".$name );
		$App->set('my.name', $name);
		$App->init( );
		return $App;
	}

	/**
	 * Initialize
	 */
	function init( )
	{
		$CH = NyaaCache::current( );
		if( self::RELEASE === false || false === $AppInfo = $CH->get('appinfo', $option) ){
			$AppInfo = unserialize(file_get_contents($this->Conf->rootDir.'/var/installed'));
			$CH->set('appinfo', $AppInfo);
		}
		$this->AppInfo = $AppInfo;

		// Get Current Application Name
		$this->set('env.app', $this->parseAppName($this->Request->getOr('app', $this->Conf->appDefault)));
	}

	function run( )
	{
		// If Handler returns false stop process
		if( false === $this->runHandler(self::WHEN_BEFORE_RUN, $this) ) return true;
		$App = $this->appFactory( $this->get('env.app') );
		$App->setRequest( $this->Request);
		if( false === $this->runHandler(self::WHEN_BEFORE_APP_RUN, $this, $App) ) return true;
		$this->runApp( $App );
		if( false === $this->runHandler(self::WHEN_AFTER_APP_RUN, $this, $App) ) return true;
		if( false === $this->runHandler(self::WHEN_AFTER_RUN, $this, $App) ) return true;
	}

	function runApp( $App )
	{
		echo $App->run();
	}

	function parseAppName( $name )
	{
		return preg_replace('/#(.*)/e', '$this->Conf->get(\'app.map.\1\')', $name);
	}


	/**
	 * Session Set
	 */
	function setSession( &$session )
	{
		$this->Session = new NyaaStoreSession($session);
		return $this->Session;
	}

	/**
	 * Cookie Set
	 */
	function setCookie( &$cookie )
	{
		$this->Cookie = new NyaaStoreCookie($cookie);
		return $this->Cookie;
	}

	/**
	 * Set Request
	 */
	function setRequest( $path )
	{
		$args = func_get_args( );
		array_shift($args);

		$this->Request = new NyaaStoreRequest( );
		$this->Request->addPathInfo( $path );
		foreach( $args as $arr) $this->Request->set( $arr );
		return $this->Request;
	}

	/**
	 * Register Handler
	 */
	function registerHandler( $when, $handler )
	{
		$this->handlers[$when][] = $handler;
	}

	function runHandler( $when )
	{
		$args = func_get_args( );
		array_shift($args);

		foreach( $this->handlers[$when] as $func )
		{
			if( false === call_user_func_array($func, $args) ){
				return false;
			}
		}
	}


	function dump( )
	{
		parent::dump( );
		echo "Session";
		$this->Session->dump( );
		echo "Cookie";
		$this->Cookie->dump( );
		echo "Request";
		$this->Request->dump( );
	}
}
?>
