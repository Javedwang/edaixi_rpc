<?php
namespace App\Lib\Rpc;
use  App\Rpc\Model as RpcModel;

class Apimodel
{
	public $data= null;
	public $obj_chain_methods = [];
	public function __construct()
	{

	}
	public function __get($attr)
	{
		if(isset($this->data->$attr))
		{
			return $this->data->$attr;
		}
	}
	public function __set($attr,$v)
	{
		$this->data->$attr = $v;
	}
	public function put($data)
	{
		$this->data = $data;
	}
	public static function __callStatic($name, $args)
	{
		$callclass = get_called_class();
		if($callclass::$structure->results->class_methods->$name == 'get' || $callclass::$structure->results->class_methods->$name == 'post' )
		{
			$methods[] = array($name, $args);
			return Handler::ClassResult($callclass::host.$callclass::api_url,$methods);

		}elseif($callclass::$structure->results->class_methods->$name == 'chain_methods' )
		{

			array_push($callclass::$chain_methods,array($name, $args));
			return $callclass;

		}elseif($name == 'load')
		{
			$methods = $callclass::$chain_methods;
			$callclass::$chain_methods = [];
			return Handler::ClassResult($callclass::host.$callclass::api_url,$methods);
		}else
		{
			return 'method error';
		}
	}
	public function __call($name, $args)
	{

		$callclass = get_called_class();
		if($callclass::$structure->results->instance_methods->$name == 'get' || $callclass::$structure->results->instance_methods->$name == 'post' )
		{
			$methods[] = array($name, $args);
			$url = $callclass::host.$callclass::api_url.'/'.$this->id;
			return Handler::ObjResult($url,$methods);

		}
		elseif($callclass::$structure->results->instance_methods->$name == 'chain_methods' )
		{

			array_push($this->obj_chain_methods,array($name, $args));
			return $this;

		}elseif($name == 'load')
		{
			$url = $callclass::host.$callclass::api_url.'/'.$this->id;
			$methods = $this->obj_chain_methods;
			$this->obj_chain_methods = [];
			return Handler::ObjResult($url,$methods);
		}else
		{
			return 'method error';
		}
	}
	public static function init()
	{
		$callclass = get_called_class();
		if(empty($callclass::$structure))
		{
			 	$url = $callclass::host.$callclass::api_url.$callclass::structure_url;
			 	$json = Handler::structure($url);
			 	return $callclass::$structure = json_decode($json);
		}
	}
}










