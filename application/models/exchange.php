<?php defined('SYSPATH') or die('No direct script access.');

class Exchange_Model extends ORM {
	
	protected $sorting = array('added' => 'desc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	
		if(empty($id)):
			$this->added = date::getDateForDb();
		endif;
	}
	
	public function __get($column){
	
		switch((string) $column):
		
			case 'url_edit':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/adm/exchange/'.$this->id.'/';
				}
				return $this->object[$column];
			break;
			case 'ue':
				if (!isset($this->object[$column])) {
					$this->object[$column] = $this->{Lib::config('payment.ue')};
				}
				return $this->object[$column];
			break;
			
		endswitch;
	
		return parent::__get($column);
	
	}

	public function save(){
//		$this->updated = date::getForDb();
		return parent::save();
	}
	
	public function getCurrent(){
	
		$this->where(array(
				'added <=' => date::getDateForDb()
			)
		);
			
		$this->orderby('added','desc');

		return $this->find();
		
	}
	
	
	
}
/* ?> */