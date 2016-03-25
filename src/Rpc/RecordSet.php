<?php
namespace App\Lib\Rpc;

class RecordSet implements \Iterator
{
    private $_list = array();
    private $_current_id = 0;
    private $model_name;

    function __construct($model_name,$list)
	{
		$this->model_name = $model_name;
		$this->_list = $list;

	}
	function get()
	{
		return $this->_list;
	}
	 public function rewind() {
       		 $this->_current_id = 0;
    	  }
	public function key()
	{
		return $this->_current_id;
	}
	public function current()
	{
		$model_name = $this->model_name;
		$m = 'App\Rpc\Model\Rpc'.$model_name;
		$record = new $m();
		$record->put($this->_list[$this->_current_id]);
		return $record;
	}
	public function next()
	{
		$this->_current_id++;
	}
	public function valid()
	{
		if(isset($this->_list[$this->_current_id])) return true;
		else return false;
	}
}
