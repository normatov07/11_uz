<?php defined('SYSPATH') or die('No direct script access.');

class User_Changed_Email_Model extends ORM {
	
	protected $belongs_to = array('user');	
	
	protected $sorting = array('user_id'=>'asc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
		
		if(empty($id)):
			$this->generate_confirmation_code();
		endif;
	}

	public function generate_confirmation_code() {
		$this->confirmation_code =  sha1(text::random('alnum', 15));
	}
	
	

}
/* ?> */