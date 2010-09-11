<?php
/**
 *
 */
class NyaaSessionHandler
{
	function __construct( )
	{
		$this->mm = NyaaCache::current( );
	}

	function open( $path, $name )
	{
		$this->name = $name;
	}

	function close( )
	{
	}
	function read($id)
	{
		return (string) $this->mm->get($this->name.'-'.$id);
	}

	function write($id, $data)
	{
		return $this->mm->set($this->name.'-'.$id, $data);
	}

	function destroy( $id )
	{
		return $this->mm->delete($this->name.'-'.$id);
	}

	function gc($maxlifetime)
	{
	}
}
?>
