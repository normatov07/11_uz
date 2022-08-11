<?php defined('SYSPATH') or die('No direct script access.');

class List_Item_Model extends ORM {

	protected $belongs_to = array('list');
	
	protected $sorting = array('priority' => 'asc', 'title'=>'asc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}
	
}
/* ?> */