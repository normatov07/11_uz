<?php defined('SYSPATH') or die('No direct script access.');

class Subway_Model extends ORM {

	protected $has_many = array('offers');	
	
	protected $belongs_to = array('region');
	
	protected $sorting = array('title' => 'asc');
	
	protected $delete_belongings_exception = array('offers');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}

}
/* ?> */