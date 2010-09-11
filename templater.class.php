<?php
/**
 * FrameWork Templater
 */
require_once 'templater/templater.default.class.php';

class NyaaFWTemplater extends NyaaTemplaterDefault
{
	public $fw;

	function __construct( $fw )
	{
		$this->fw             = $fw;
		$this->templateDir    = $fw->Conf->templateDir;
		$this->cacheDir       = $fw->Conf->templateCache;
		$this->leftDelimiter  = '{{';
		$this->rightDelimiter = '}}';
		$this->registerCompilerHandler ( 'snippet',   new NyaaFWTemplaterSnippet( ));
		parent::__construct( );
	}
}

/**
 * Snippet Template Compiler
 * ----
 * Type: Template Plugin
 */
class NyaaFWTemplaterSnippet extends NyaaTemplaterCompiler
{
	function isBlock( $name, $opt)
	{
		if( "/" == $opt[strlen($opt)-1]) {
			return false;
		}
		return true;
	}

	function compile( $name, $opt, $text, $templater)
	{
		$opt  = $templater->getOptExported($opt);
		$ret  = "";
		$ret .= '<?php'."\n";
		$ret .= 'ob_start();';
		$ret .= '?>';
		$ret .= $text;
		$ret .= '<?php'."\n";
		$ret .= '$contents = ob_get_clean();'."\n";
		$ret .= 'echo $this->fw->snippet('.$opt.',$contents,$this);'."\n";
		$ret .= '?>';
		return $ret;
	}
}
?>
