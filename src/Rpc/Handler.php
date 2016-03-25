<?php
namespace App\Lib\Rpc;
use Requests;

class Handler
{
	public static function HandleResult($r)
	{
		$der =  json_decode($r->body);
		$type = $der->model;
		//var_dump($r);
		switch ($type) {
			case 'boolean':
				# code...
				$result = $der->results;
				break;
			case 'integer':
				# code...
				$result = $der->results;
				break;
			case 'double':
				$result = $der->results;
				break;
			case 'string':
				$result = $der->results;
				break;
			case 'array':
				$result = $der->results;
				break;

			case 'collection':
				// $result = $der->results;
				$result   = new RecordSet($der->model,$der->results);
				break;
			case 'object':
				$result = $der->results;
				break;
			default:
				if(is_object($der->results))
				{
					$model_name= $der->model;
					$m = 'App\Rpc\Model\Rpc'.$model_name;
					//var_dump($m);
					$result = new $m();
					$result ->put ($der->results);
					break;
				}
				elseif(is_array($der->results))
				{
					$result   = new RecordSet($der->model,$der->results);
					break;
				}
				else
				{
					$result = 'error';
					break;
				}
			}
		return $result;
	}
	public static function ClassResult($url,$methods)
	{
		$result = Requests::post($url, array(),array('methods'=>json_encode($methods)), array());
		return self::HandleResult($result);
	}
	public static function ObjResult($url,$methods)
	{
		$result = Requests::post($url, array(),array('methods'=>json_encode($methods)),array());
		return self::HandleResult($result);

	}
	public static function structure($url)
	{
		$result = Requests::get($url,array(), array());
		// var_dump($result->body);
		return $result->body ;

	}
	public static function getRequest($key) {
		$request = null;
		if (isset ( $_GET [$key] ) && ! empty ( $_GET [$key] )) {
			$request = $_GET [$key];
		} elseif (isset ( $_POST [$key] ) && ! empty ( $_POST [$key] )) {
			$request = $_POST [$key];
		}
		return $request;
	}
}






