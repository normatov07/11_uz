<?php defined('SYSPATH') or die('No direct script access.');

class Ro_Client_Model extends ORM {

	protected $has_many = array('ros');	
	protected $delete_belongings_exception = array('ros');
	
	protected $sorting = array('added' => 'desc', 'title' => 'asc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
		
	}

	public function save() {
		if(empty($id) or empty($this->added)):
			$this->added = date::getForDb();
		endif;
		return parent::save();
	}


}
/* ?> */