<?php defined('SYSPATH') or die('No direct script access.');

class Tariff_Model extends ORM {
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	
		if(empty($id)):
			$this->setUpdated();
		endif;
	}
	
	public function setUpdated() {
		$this->updated = date::getForDb();
	}

}
/* ?> */