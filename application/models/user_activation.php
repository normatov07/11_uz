<?php defined('SYSPATH') or die('No direct script access.');

class User_Activation_Model extends ORM {

	protected $belongs_to = array('user');
	
	private $conf;
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
		
		$this->conf = Lib::config('auth');
		if(empty($id)) $this->generate_activation_key($this->conf['activation_key_length']);		
		
	}

		
	private function generate_activation_key($len = 30) {
		$this->activation_key = text::random('alnum', $len);
	}
	

}
/* ?> */