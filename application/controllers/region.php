<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Region Controller.
 */
class Region_Controller extends Controller {
	
	public function __construct(){
	
		parent::__construct();
/*		
		if(!request::is_ajax()):
			$this->view = new View('category_view');
			$this->template->titleInView = true;
			
			$this->template->yandex_direct = 5;
			
			$this->parent_title = 'Объявления';
			
			$this->perpage = 20;
//			$this->title = 'Разделы';
			$this->addJs('search.js');
			$this->addJs('breadcrumbs.js');
			
		endif; 
		*/
	}
		
	public function index($category_id = NULL, $page = 0){
		
		return Lib::pagenotfound();
		
		if(empty($category_id) and !empty($_GET['id'])) $category_id = $_GET['id'];
		
		if(!empty($category_id)):
			if(preg_match('/^\d+$/', $category_id)):
				$this->category = ORM::factory('category', $category_id);
			else:
				$this->category = ORM::factory('category')->find(array('codename' => $category_id));
			endif;
		endif;

		if(empty($category_id) or $this->category->id == 0):

			Lib::pagenotfound();
			return false;
			
		endif;		

/**
 * Выставляем заголовок
 */
		$parentsCount = $this->category->parents->count();
		
		if($parentsCount > 1):
			$this->title = $this->category->parents[$parentsCount-2]->title . ': '. $this->category->parents[$parentsCount-1]->title;
		else:
			$this->title = $this->category->title;
		endif;

		if(!empty($this->category->description)) $this->page_description = $this->category->description;
		
		$this->template->category_id = $this->category->id;
		
		if($this->category->where('status','enabled')->children->count()):
			$this->view->categoriesList = $this->category->children;
		else:
			$this->view->categoriesList = $this->category->where('status','enabled')->siblings;
			$this->view->category_has_no_children = true;
		endif;
		
		$this->view->types = ORM::factory('type')->find_all()->as_id_array();
		
		$this->view->filters = $this->category->getFilters();
		
		if(!empty($this->view->category_has_no_children)):
			$this->view->regions = ORM::factory('region')->find_all()->select_list();
		else:			
			$this->view->categories = $this->category->where('status','enabled')->getFullSubList()->as_id_array();
		endif;
		
		$this->view->obj = $this->category;
		
		$this->view->categorytypes = $this->category->types->select_list();
		
		if(count($this->view->categorytypes)>1) $this->view->categorytypes = array(''=>'любой') + $this->view->categorytypes;
		
		$this->view->page = $page;
		
		
		
		$this->view->childrenIDs = $this->category->getChildrenIDs(true);
	
		$offers = ORM::factory('offer');

		if($this->view->offersCount = $offers->setFilters($_REQUEST)->count_all_In_Category($this->view->childrenIDs)):

			$paginationConfig = array(
				'total_items'    => $this->view->offersCount, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
//				'item_title'		=> array('объявление','объявления', 'объявлений'),
				'group' => 'fe_list',
			);
	
			$pagination = new Pagination($paginationConfig);
	
			$this->view->pagination = $pagination;
		
			$this->view->offerList = $offers->setFilters($_REQUEST)->find_all_In_Category($this->view->childrenIDs, $this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);

			$offerIDs = array_keys($this->view->offerList->select_list());
			if(count($offerIDs)) $this->view->pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);
			
		endif; // offersCount
				
	}	
	
	public function get($obj_id = 0){
	
		$this->obj = new Region_Model($obj_id);
		
		if($this->obj->has_district and $this->obj->districts->count()):
			if(empty($this->data)) $this->data = array();
			$this->data['district_title'] = Lib::config('app.district_type', $this->obj->district_type);
			$this->data['districts'] = array();
			foreach($this->obj->districts as $item):
				$this->data['districts'][] = array('id'=>$item->id, 'title'=>$item->title);
			endforeach;
		endif;
		
		if($this->obj->has_subway and $this->obj->subways->count()):
			if(empty($this->data)) $this->data = array();
			$this->data['subways'] = array();
			foreach($this->obj->subways as $item):
				$this->data['subways'][] = array('id'=>$item->id, 'title'=>$item->title);
			endforeach;
		endif;
			
		if(!request::is_ajax()){
			print_r($this->data);
			exit;
		}

	}


}
/* ?> */