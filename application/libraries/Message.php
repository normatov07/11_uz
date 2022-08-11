<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MESSAGES HANDLING LibRARY
 */

class Message_Core{

	public $mode;
	public $messages = array();
	public $accessDeniedSet = false;
	
	public function __construct($mode = 'error'){
		$this->$mode = $mode;
	}
	
	public function add($str, $key = NULL){
		if(is_array($str)) $this->messages = array_merge($this->messages, $str);
		elseif($key != NULL) $this->messages[$key] = $str;
		else $this->messages[] = $str;
	}
	
	public function get(){
		return $this->messages;
	}	
	
	public function is_empty(){
		if(!count($this->messages)) return true;
		return false;
	}
	
}

/* ?> */