<?php defined('SYSPATH') or die('No direct script access.');

class Region_Model extends ORM {

	protected $has_many = array('offers', 'users', 'districts', 'subways');	
	protected $has_and_belongs_to_many = array('users');
	protected $sorting = array('priority' => 'asc', 'title'=>'asc');
	
	protected $delete_belongings_exception = array('users');
	
	protected $has_priority = true;
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}

	public function __get($column){
	
		switch((string) $column):
		
			case 'has_district':
				if (!isset($this->object[$column])) {
					$this->object[$column] = $this->district_type != '';
				}
				return $this->object[$column];
			break;
			
		endswitch;
	
		return parent::__get($column);
	}

}
/* ?> */