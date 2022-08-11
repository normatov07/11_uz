<?php defined('SYSPATH') or die('No direct script access.');

class Status_change_Model extends ORM {

	protected $belongs_to = array('user','offer');	
	
	protected $sorting = array('added' => 'desc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
		
		if(empty($id)):
			$this->added = date::getForDb();
		endif;
	}

}
/* ?> */