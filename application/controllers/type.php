<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Category Controller.
 */
class Type_Controller extends Controller {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!request::is_ajax()):
			$this->view = new View('type_view');
			$this->template->titleInView = true;
			$this->template->yandex_direct = 5;
			
			$this->parent_title = 'Объявления';
			
			$this->perpage = 30;
			$this->addJs('breadcrumbs.js');
			
			
		endif;
		
	}
		
	public function index($type_id = NULL, $page = 0){
		
		if(!empty($_GET['type_id'])) $type_id = $_GET['type_id'];
		
		if(!empty($type_id)):
			if(preg_match('/^\d+$/', $type_id)):
				$this->type = ORM::factory('type', $type_id);
			else:
				$this->type = ORM::factory('type')->find(array('codename' => $type_id));
			endif;
		endif;

		if(empty($this->type)){
			$this->type = new Type_Model;
			$this->type->title = $this->type->other_title = 'Все объяления';
			$this->type->has_price = true;
		}
		$this->view->obj = $this->type;
/**
 * Выставляем заголовок
 */
		
		$this->title = $this->type->other_title;

		$this->view->types = ORM::factory('type')->find_all()->as_id_array();
		
		$offers = ORM::factory('offer');
		
		$filters = array();
	
		if($this->type->id != NULL) $filters['type_id'] = $this->type->id;
		
		if($this->view->offersCount = $offers->setFilters($filters)->count_all_In_Category(0)):

			$paginationConfig = array(
				'total_items'    => $this->view->offersCount, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
				'group' => 'fe_list',
			);
	
			$pagination = new Pagination($paginationConfig);
			
			$this->view->pagination = $pagination;
		
			$this->view->offerList = $offers->setFilters($filters)->orderby('added','desc')->find_all_In_Category(0, $this->perpage, ($pagination->current_page > 0?$pagination->current_page - 1:0) * $this->perpage);
		
			$offerIDs = array_keys($this->view->offerList->select_list());
			if(count($offerIDs)) $this->view->pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);
			
			if($categoryIDs = array_keys($this->view->offerList->getValues('category_id'))):
				$this->view->categories = ORM::factory('category')->in('id', $categoryIDs)->find_all()->as_id_array();
			endif;
			
		endif; // offersCount
		
			
	}


}
/* ?> */