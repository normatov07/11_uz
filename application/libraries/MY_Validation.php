<?php defined('SYSPATH') or die('No direct script access.');

class Validation extends Validation_Core {


	public function __construct(array $array)
	{
		foreach($array as $key => $val):
			if(!is_array($val)) continue;
			foreach($val as $k => $v):
				$array[$key.'['.$k.']'] = $v;
			endforeach;
		endforeach;
		
		parent::__construct($array);
	}

	public function error_message($key, $field = NULL){
		$string = Kohana::lang('validation.'.$key);
		if($string != '' && $string != 'validation.'.$key)
			return sprintf(Kohana::lang('validation.'.$key), '"'.$field.'"');
		else 
			return $key;
	}
	
	public function list_errors(){
		$list = array();
		foreach($this->errors() as $field=>$key):
			$list[] = $this->error_message($key, $field);
		endforeach;
		return $list;
	}
	
}

/* ?> */