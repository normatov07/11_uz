<?php defined('SYSPATH') or die('No direct script access.');

class Data_Model extends ORM {

	protected $belongs_to = array('offer', 'property');	
	
	protected $primary_val = 'datavalue';
	
//	protected $sorting = array('priority' => 'asc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}
}
/* ?> */