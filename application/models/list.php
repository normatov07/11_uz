<?php defined('SYSPATH') or die('No direct script access.');

class List_Model extends ORM {

	protected $has_many = array('list_items', 'properties');
	
	protected $sorting = array('title'=>'asc');
	
	public $items = array();
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}
		
	public function save_with_related(){
	
		$this->save();

		$i = 0;	
		foreach($this->list_items as $item):
			if(isset($this->items[$i])):				
				$item->title = $this->items[$i]->title;
				$item->valuedata = $this->items[$i]->valuedata;
//				$item->isdefault = @$this->items[$i]->isdefault;
				$item->save();
			else:
				$item->delete();
			endif;
			$i++;
		endforeach;
		
		while(isset($this->items[$i])):
			$item = ORM::factory('list_item');
			$item->title = $this->items[$i]->title;
			$item->valuedata = $this->items[$i]->valuedata;
			$item->isdefault = @$this->items[$i]->isdefault?1:0;
			$item->priority = $i;
			$item->{$this->foreign_key()} = $this->id;
			$item->save();
			$i++;
		endwhile;	
		return true;	
	}
	
	public function get_titles_list(){
		$this->orderby('title','asc');
		$this->select('id','title','isquick');
		return $this->find_all();
	}
	
	
}
/* ?> */