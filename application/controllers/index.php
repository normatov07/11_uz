<?php defined('SYSPATH') or die('No direct script access.');

class Index_Controller extends Controller {

	public function __construct(){
	
		parent::__construct();
		$this->view = new View('index_view');
		$this->template->yandex_direct = 5;
		$this->template->hp_title='AliBaba';
		$this->page_description = 'AliBaba';
	}

	public function index(){

		$this->view->catalogRootCount = ORM::factory('category')->where('status','enabled')->countOnLevel(1);
		$this->view->catalog = ORM::factory('category')->find_all_enabled_cached();

		//$this->view->categories = $this->view->catalog->as_id_array();
		
		$this->view->types = ORM::factory('type')->find_all();
		
		$offerList = array();
		$offerIDs = array();

		foreach($this->view->types as $type):
			if(!$type->on_home) continue;
			
			$offers = ORM::factory('offer');
			$offerList[$type->id] = $offers->setFilters()->where('type_id', $type->id)->find_all_In_Category(0, Lib::config('app.offers_of_each_type_on_homepage'));
			
			$offerIDs = array_merge($offerIDs, array_keys($offerList[$type->id]->select_list()));

		endforeach;
        $this->view->offerList = $offerList;
		
		if(count($offerIDs)) $this->view->pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);

        // Рекламные объявления
		$rolist = ORM::factory('ro')->find_in_category('HOME', TRUE);
		if(count($rolist)):
			$this->view->rolist = new View('b_ro_main_view');
			$this->view->rolist->list = $rolist;
		endif;		
	}

}