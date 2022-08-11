<?php defined('SYSPATH') or die('No direct script access.');

class RO_Model extends ORM {

	protected $belongs_to = array('category', 'ro_agent', 'ro_client');
	
	protected $has_many = array('pictures');	
	
	protected $has_and_belongs_to_many = array('categories');
	
	protected $sorting = array('added' => 'desc');
	
	public $pic;
	
	public $best_date;

	public function __construct($id=NULL)
	{
		parent::__construct($id);

		$this->best_date = new date();
		
		if(empty($id)):
			$this->added = $this->best_date->getForDb();
		endif;		
	}

		
	public function __get($column){
	
		switch((string) $column):
		
			case 'url':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/ro/'.$this->id.'/';
				}
				return $this->object[$column];
			break;
			case 'url_print':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url.'?print=1';
				}
				return $this->object[$column];
			break;
			case 'url_edit':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/adm/ro/edit/'.$this->id.'/';
				}
				return $this->object[$column];
			break;
			case 'url_disable':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/adm/ro/change_status/'.$this->id.'/disabled/';
				}
				return $this->object[$column];
			break;
			case 'url_enable':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/adm/ro/change_status/'.$this->id.'/enabled/';
				}
				return $this->object[$column];
			break;
			case 'url_delete':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/adm/ro/delete/'.$this->id.'/';
				}
				return $this->object[$column];
			break;
			case 'state':
			case 'state_title':
				if (!isset($this->object[$column])) {
					if($this->status == 'disabled'):
						$this->object['state'] = 'disabled';
						$this->object['state_title'] = 'черновик';
					elseif($this->date_start > $this->best_date->getDateForDb()):
						$this->object['state'] = 'onhold';
						$this->object['state_title'] = 'на публикацию';
					elseif($this->date_end < $this->best_date->getDateForDb()):
						$this->object['state'] = 'expired';
						$this->object['state_title'] = 'завершено';
					else:
						$this->object['state'] = 'enabled';
						$this->object['state_title'] = 'активно';
					endif;
				}
				return $this->object[$column];
			break;
		endswitch;
	
		return parent::__get($column);
	
	}
	

	public function save() {
		$this->updated = date::getForDb();
		return parent::save();
	}
	
	public function find_in_category($category_id = NULL, $withpic = false){
		if($category_id == NULL) return;
		
		if($category_id == 'HOME'):
			$this->where('onhome', 1); 
		else:
			$this->join('categories_ros as cr', 'ros.id', 'cr.ro_id');
			$this->where('cr.category_id', $category_id);  
		endif;
		
		$list = $this
			->where('status', 'enabled')
			->where('date_start <=', $this->best_date->getDateForDb())
			->where('date_end >=', $this->best_date->getDateForDb()) 
			->orderby('RAND()')
			->find_all()->as_id_array();
		
		if($withpic and count($list)):
			$pics = ORM::factory('picture')->in($this->foreign_key(), array_keys($list))->find_all()->as_id_array($this->foreign_key());
			if(count($pics)):
				foreach($list as $key => $item):
					if(!empty($pics[$item->id])):
						$list[$key]->pic = $pics[$item->id];
					endif;
				endforeach;
			endif;
		endif;
		return $list;
		
	}

}
/* ?> */