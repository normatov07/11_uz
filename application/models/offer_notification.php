<?php defined('SYSPATH') or die('No direct script access.');

class Offer_Notification_Model extends ORM {

	protected $belongs_to = array('offer');	
	
//	protected $sorting = array('priority' => 'asc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
		
		if(empty($id)):
			$this->added = date::getForDb();
		endif;
	}

}
/* ?> */