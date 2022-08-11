<?php defined('SYSPATH') or die('No direct script access.');

class Pagination extends Pagination_Core {

	protected $max_pages;	
	protected $item_title;	

	public function initialize($config = array()){

		parent::initialize($config);
		
		if(!empty($this->max_pages) and $this->total_items > ($max_results = $this->max_pages * $this->items_per_page)):
			$config['total_items'] = $max_results;
			parent::initialize($config);
		endif;
				
	}
	
	
	
}

/* ?> */