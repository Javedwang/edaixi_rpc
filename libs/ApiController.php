<?php
namespace App\Lib;
use Swoole;
use App\Model as Model;

class ApiController extends Swoole\Controller
{
	const  zombie_model_structure  = array(
		"results"=>array(
			"class_methods" => array(
				"first"=>"chain_methods",
				"get"=>"chain_methods",
				"count"=>"chain_methods",
				"find"=>"post",
				"last"=>"post",
				"create"=>"post",
				"where"=>"chain_methods",
				"whereIn"=>"chain_methods",
				"select"=>"chain_methods",
				"limit"=>"chain_methods",
				"getResults"=>"chain_methods"
				),
			"instance_methods"=>array(
				"getResults"=>"chain_methods",
				),
			"model_name"=> "",
			"attributes"=>array(
				)
			),
		"model"=>"",
		"model_sign"=> ""
		);
	public function __construct(\Swoole $swoole)
	{
		parent::__construct($swoole);
		$this->init();
		$this->methods = $this->getbody();
	}
	private function getbody()
	{
		$request = $this->swoole->request;
		$body = urldecode($request->post["methods"]);
		return json_decode($body);
	}
	private function init()
	{
		$this->class_name = get_class($this);
		$names = explode('\\',$this->class_name);
		$this->class_short_name = array_pop($names);
		return true;
	}
	private function getSign()
	{
		return md5(time().mt_rand(0,10000));
	}
	public function zombie_model_structure()
	{
		$zombie_model_structure = $this->get_model_structure();
		return $this->json($zombie_model_structure);
	}
	public function get_model_structure()
	{
		$class_name = $this->class_name;
		$zombie_model_structure = array_merge_recursive(self::zombie_model_structure,$class_name::child_zombie_model_structure);
		$zombie_model_structure["model"] = $this->class_short_name;
		$zombie_model_structure["model_sign"] = $this->getSign();
		return $zombie_model_structure;
	}
	public function getControllerResult($method)
	{
		$r = call_user_func_array($this->class_name.'::'.reset($method),next($method));
		return $r;

	}
	public function getModelResult($methods)
	{
		$zombie_model_structure  =  $this->get_model_structure();
		$mode_name = '\\App\\Model\\'.$this->class_short_name;
		$r = 'class';
		foreach ($methods  as $key =>$value)
		{
			if($r == 'class' && isset($zombie_model_structure['results']['class_methods'][reset($value)]))
			{
				 $r = call_user_func_array($mode_name.'::'.reset($value),next($value));

			}elseif(is_object($r) && isset($zombie_model_structure['results']['class_methods'][reset($value)]) && $zombie_model_structure['results']['class_methods'][reset($value)] == 'chain_methods' )
			{
				$r = call_user_func_array(array($r,reset($value)),next($value));
			}else
			{
				break;
			}
			
		}
		return $r;
	}
	public function getModelObjResult($methods)
	{
		$zombie_model_structure  =  $this->get_model_structure();
		$model_name = '\\App\\Model\\'.$this->class_short_name;
		$r = 'obj';
		 $obj = call_user_func_array($model_name.'::find',array($_GET['id']));
		foreach ($methods  as $key =>$value)
		{
			 $name = reset($value);
			 $para = next($value);
			if($r == 'obj' && isset($zombie_model_structure['results']['instance_methods'][$name]))
			{
				 $r = call_user_func_array(array($obj,$name), $para);

			}elseif(is_object($r) && isset($zombie_model_structure['results']['instance_methods'][$name]) && $zombie_model_structure['results']['instance_methods'][$name] == 'chain_methods' )
			{
				$r = call_user_func_array(array($r,$name), $para);
			}else
			{
				break;
			}
			
		}
		return $r;
	}
	public function class_call()
	{
		$methods = $this->methods;
		$first_method = reset($methods);
		if(!empty($methods))
		{
			if(method_exists($this->class_name, reset($first_method)))
			{
				$r = $this->getControllerResult($first_method);
			}else{
				$r = $this->getModelResult($methods);
			}
		}
		$response = $this->get_response($r);
		return $this->json($response);
	}
	public function model_call()
	{
		$methods = $this->methods;
		$class_name = $this->class_name;
		if(!empty($methods))
		{
			$first_method = reset(reset($methods));
			$first_value = next(reset($methods));
			if(method_exists($this, $first_method))
			{
				$r = $this->$first_method($first_value);
			}else{

				$r = $this->getModelObjResult($methods);
			}
		}
		$response = $this->get_response($r);
		return $this->json($response);
	}
	function json($data)
	{
            	            $this->http->header('Cache-Control', 'no-cache, must-revalidate');
	            $this->http->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
	            $this->http->header('Content-Type', 'application/json');
	            return json_encode($data);
	}
	function get_response($data)
	{
		$type = gettype($data);
		$regx = '^App\\\Model\\\([a-zA-Z]+)$';
		$response = array(
			'results' =>null,
			'model' => $this->class_short_name,
			"model_sign" =>$this->getSign()
			);
		switch ($type) {
			case 'boolean':
				$result = $data;
				$s_name = 'boolean';
				break;
			case 'integer':
				$result = $data;
				$s_name = 'integer';
				break;
			case 'double':
				$result = $data;
				$s_name = 'double';
				break;
			case 'string':
				$result = $data;
				$s_name = 'string';
				break;
			case 'object':
				$object_class_name = get_class($data);
				if($object_class_name == 'Illuminate\Database\Eloquent\Collection')
				{
					$l_name = get_class($data->random());
					if(preg_match('#'.$regx.'#i',$l_name,$match))
					{
						
						$s_name = next($match);
					}
					$result = $data->toArray();
				}elseif(preg_match('#'.$regx.'#i',$object_class_name,$match)){
					$result = $data->toArray();
					$s_name = next($match);
				}else
				{
					$result = $data;
					$s_name = 'object';
				}
				break;
			case 'array':
				$result = $data;
				$s_name = 'array';
				break;
			default:
				$result = 'typeerror';
				$s_name = 'typeerror';
				break;
		}
		$response['results'] = $result;
		if(!empty($s_name))
		{
			$response['model'] = $s_name;
		}
		return $response;
	}
}