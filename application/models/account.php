<?php defined('SYSPATH') or die('No direct script access.');

class Account_Model extends ORM {

	protected $belongs_to = array('user');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	
		if(empty($id)):
			$this->setUpdated();
		endif;
	}
	
	public function setUpdated() {
		$this->updated = date::getForDb();
	}

}
/* ?> */