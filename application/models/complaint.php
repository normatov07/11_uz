<?php defined('SYSPATH') or die('No direct script access.');

class Complaint_Model extends ORM {

	protected $belongs_to = array('user', 'offer');
	
	protected $sorting = array('added' => 'desc');	
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
		
		if(empty($id)):
			$this->added = date::getForDb();
			$this->status = 'new';
			$this->ip_address = Input::instance()->ip_address();
		endif;
	}

	public function __get($column){
	
		switch((string) $column):
			case 'url':
				if (empty($this->object[$column])) {
					$this->object[$column] = '/adm/complaint/' . $this->id .'/';
				}
				return $this->object[$column];
			break;
			case 'url_reply':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url . 'reply/';
				}
				return $this->object[$column];
			break;
		endswitch;
	
		return parent::__get($column);
	
	}

}
/* ?> */