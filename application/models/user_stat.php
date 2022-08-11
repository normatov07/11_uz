<?php defined('SYSPATH') or die('No direct script access.');

class User_Stat_Model extends ORM {

	protected $belongs_to = array('user');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}

}
/* ?> */