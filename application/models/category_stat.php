<?php defined('SYSPATH') or die('No direct script access.');

class Category_Stat_Model extends ORM {

	protected $belongs_to = array('category');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}

}
/* ?> */