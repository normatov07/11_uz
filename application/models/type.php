<?php 

class Type_Model extends ORM {

	protected $has_many = array('offers','title_formats');	
	protected $has_and_belongs_to_many = array('categories');	
	
	protected $sorting = array('priority' => 'asc', 'title' => 'asc');
	
	protected $has_priority = true;
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}

	public function __get($column)
	{	
		switch((string) $column):
			case 'url':

				if (empty($this->object[$column])) {
					$this->object[$column] = '/type/' . ($this->codename?$this->codename:$this->id).'/';
				}
				return $this->object[$column];
				
			break;
		endswitch;

		return parent::__get($column);
	}

}
/* ?> */