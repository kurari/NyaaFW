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
		$Tpl = $this->FW->getTemplater( );
		$Tpl->set('request',$this->Request->get());
		return $Tpl;
	}

	function init( )
	{

	}

	function setRequest( $Req )
	{
		$this->Request = $Req;
	}

	function run( )
	{
		$tpl = $this->myName.'.html';
		return $this->getTemplater( )->fetch($tpl);
	}

	function formFactory( $conf, $key )
	{
		require_once 'form/form.class.php';
		$form     = new NyaaForm( );
		$form->loadFile($conf);
		$form->addHidden('__FORM_ID__', $key);
		return $form;
	}

	function validaterFactory( $conf )
	{
		require_once 'validater/validate.class.php';
		require_once 'validater/validater.class.php';

		$conf = NyaaConf::load( $conf );
		$validater = new NyaaValidater( );
		foreach($conf->get( ) as $k=>$v)
		{
			$validate = NyaaValidate::factory(
				array(
					'type'    => $v['type'],
					'target'  => $v['target'],
					'message' => $v['message'],
					'con'     => $v['message_sep']
				)
			);
			$validater->addValidate( $validate );
		}
		return $validater;
	}

	function doSnippet( $method, $option )
	{
		return call_user_func( array($this,'snip'.ucfirst($method)), $option);
	}

}
?>
