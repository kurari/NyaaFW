<?php
/**
 * If Url is Resource then
 */
class NyaaFWHandlerResource extends NyaaFWHandler
{
	private $key = 'resource';

	function __construct( )
	{

	}

	function trigger( $fw )
	{
		if($fw->Request->getOr('path_info.1','') == $this->key){
			$dir = $fw->Conf->resource[$fw->Request->path_info[2]];
			$file =  $fw->Request->slicePath($fw->Request->path_info[2]);
			echo file_get_contents( $dir.'/'.$file );
			return false;
		}
		return true;
	}
}

?>
