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

	public $Conf;
	public $Session;
	public $Request;
	public $Cookie;

	public $Appmap;

	public $handlers;

	/**
	 * factory
	 *
	 * @param string filepath
	 * @param array option {typt,}
	 */
	public static function factory( $file, $option )
	{
		$Conf = NyaaConf::load($file, $option);
		$type = $Conf->getOr('fw.type','web');
		$file = dirname(__FILE__)."/fw.$type.class.php";
		$class = 'NyaaFW'.ucfirst($type);
		require_once $file;
		$fw = new $class( $Conf );
		$appmap = NyaaFWAppmap::factory( $Conf->getOr('fw.appmap', 'default'), $fw);

		$fw->setAppmap( $appmap );
		return $fw;
	}

	function __construct( )
	{
		parent::__construct( );
		$this->handlers[self::WHEN_BEFORE_RUN] = array();
		$this->handlers[self::WHEN_AFTER_RUN] = array();
		$this->handlers[self::WHEN_BEFORE_APP_RUN] = array();
		$this->handlers[self::WHEN_AFTER_APP_RUN] = array();
	}

	/**
	 * Initialize
	 */
	function init( )
	{
		$appName = $this->Appmap->getCurrentAppName( );
		$this->set('env.app', $appName);
	}

	function run( )
	{
		// If Handler returns false stop process
		if( false === $this->runHandler(self::WHEN_BEFORE_RUN, $this) )
			return true;
		$App = $this->appFactory( $this->get('env.app') );
		if( false === $this->runHandler(self::WHEN_BEFORE_APP_RUN, $this, $App) )
			return true;
		$App->run( );
		if( false === $this->runHandler(self::WHEN_AFTER_APP_RUN, $this, $App) )
			return true;

		if( false === $this->runHandler(self::WHEN_AFTER_RUN, $this, $App) )
			return true;
		$this->dump( );
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
	 * Set App Mapper
	 */
	function setAppmap( $appmap )
	{
		$this->Appmap = $appmap;
		return $this->Appmap;
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


	function appFactory( $name )
	{
		list($name,$file,$class) = $this->Appmap->getAppInfo( $name );
		require_once $file;
		$App =  new $class( $this );
		$App->set('my.url', $this->Appmap->createUrl( $name ));
		$App->set('my.name', $name);
		$App->init( );
		return $App;
	}

	function dump( )
	{
		echo "Session";
		$this->Session->dump( );
		echo "Cookie";
		$this->Cookie->dump( );
		echo "Request";
		$this->Request->dump( );
	}
}
?>
