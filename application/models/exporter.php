<?php defined('SYSPATH') or die('No direct script access.');

class Exporter_Model extends ORM {

	protected $sorting = array('title' => 'asc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}

}
/* ?> */