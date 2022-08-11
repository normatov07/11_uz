<?php 

class Title_Format_Model extends ORM {

	protected $belongs_to = array('category', 'type');	
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}

}
/* ?> */