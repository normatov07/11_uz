<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Category Controller.
 */
class Category_Controller extends Controller {

	public function __construct(){

		parent::__construct();

		if(!request::is_ajax()):
			$this->view = new View('category_view');
			$this->template->titleInView = true;

			$this->template->yandex_direct = 4;

			$this->parent_title = 'Объявления';

			$this->perpage = 20;
//			$this->title = 'Разделы';
			$this->addJs('search.js');
			$this->addJs('breadcrumbs.js');
			$this->addJs('jquery.jqModal.js');
			$this->addJs('offer.js?20131101');

		endif;
	}

	public function index($category_id = NULL, $page = 0){

		
		// die($category_id);
		$this->template->page = $page;

		if(empty($category_id) and !empty($_GET['id'])) $category_id = $_GET['id'];
		// die($category_id);
		if(!empty($category_id)):
			if(preg_match('/^\d+$/', $category_id)):
				$this->category = ORM::factory('category', $category_id);
			else:
				$this->category = ORM::factory('category')->find(array('codename' => $category_id, 'status'=> 'enabled'));
			endif;
		endif;
		// die($category_id);
		if(empty($category_id) or $this->category->id == 0):
			Lib::pagenotfound();
			return false;

		endif;

/**
 * Выставляем заголовок
 */
		$parentsCount = $this->category->parents->count();

		if($parentsCount > 1):
			$this->title =  $this->category->parents[$parentsCount-1]->title .' - '. $this->category->parents[$parentsCount-2]->title;
		else:
			$this->title = $this->category->title;
		endif;

		// кодовое имя родительской директории
		$this->template->category_parent_codename = $this->category->parents[0]->codename; // передаём source в template_view для Крутилки Рекламы
		$this->view->category_parent_codename = $this->category->parents[0]->codename; // передаём source в category_view для Крутилки Рекламы
		$this->view->brand = rand(0, 10000000000000);

		if(!empty($this->category->description)) $this->page_description = $this->category->description;

		$this->template->category_id = $this->category->id;

		if($this->category->where('status','enabled')->children->count()):
			$this->view->categoriesList = $this->category->children;
		else:
			$this->view->categoriesList = $this->category->where('status','enabled')->siblings;
			$this->view->category_has_no_children = true;
		endif;

		$this->view->types = ORM::factory('type')->find_all()->as_id_array();

		if(empty($this->view->category_has_no_children)):
			$this->view->categories = $this->category->getFullSubList()->as_id_array();
		endif;

/**
 * Берем описание раздела для вывода внизу страницы
 */
 		if (!empty($this->category->description) and $page == 0) {
			$this->template->category_description = $this->category->description;
		}  else {
			$this->template->category_description = null;
		}

/**
 * Региональные данные
 */

		$regions = ORM::factory('region')->find_all();
		$this->view->regions = $regions->select_list();

		$current_region = !empty($_REQUEST['region_id'])?$_REQUEST['region_id']:$regions[0]->id;

		if(empty($_REQUEST['region_id'])):
			if(!empty($_REQUEST['district_id'])):
				$_REQUEST['region_id'] = $current_region;
//				unset($_REQUEST['district_id']);
			endif;
			if(!empty($_REQUEST['subway_id'])):
				$_REQUEST['region_id'] = $current_region;
//				unset($_REQUEST['subway_id']);
			endif;
		endif;

		$regions = $regions->as_id_array();

		$quickcount = 0;
        $quickfilters = [];

		if($this->category->has_district and $regions[$current_region]->has_district):

			$this->view->district_type = Lib::config('app.district_type', $regions[$current_region]->district_type);
			$this->view->districts = $regions[$current_region]->districts->select_list();


			// Если есть районы и раздел не Квартиры и комнаты и аренда квартир, то выводим их быстрым списком

			if($this->category->has_district == 2 and ($this->category->id != 9 and $this->category->id != 10)):

				$quickfilters[$quickcount]['title'] = $regions[$current_region]->title . ' - ' . $this->view->district_type;
				$quickfilters[$quickcount]['items'] = $this->view->districts;
				$quickfilters[$quickcount]['name'] = 'region_id='.$current_region.'&district_id';
				$quickfilters[$quickcount]['reset_url'] = '?region_id='.$current_region;

				if(!empty($_REQUEST['district_id'])) $quickfilters[$quickcount]['this'] = $_REQUEST['district_id'];

				$quickcount++;
			endif;

		endif;

		if($this->category->has_subway and $regions[$current_region]->has_subway):
			$this->view->subways = $regions[$current_region]->subways->select_list();

			if($this->category->has_subway == 2):

				$quickfilters[$quickcount]['title'] = $regions[$current_region]->title . ' - Станции метро';
				$quickfilters[$quickcount]['items'] = $this->view->subways;
				$quickfilters[$quickcount]['name'] = 'region_id='.$current_region.'&subway_id';
				$quickfilters[$quickcount]['reset_url'] = '?region_id='.$current_region;

				if(!empty($_REQUEST['subway_id'])) $quickfilters[$quickcount]['this'] = $_REQUEST['subway_id'];

				$quickcount++;
			endif;

		endif;

		$this->view->quickfilters = !empty($quickfilters) ? $quickfilters : [];
		$this->view->quickcount = !empty($quickcount) ? $quickcount : 0;


		$this->view->filters = $this->category->getFilters();

		$this->view->obj = $this->category;

		$this->view->categorytypes = $this->category->types->select_list();

		if(count($this->view->categorytypes)>1) $this->view->categorytypes = array(''=>'любой') + $this->view->categorytypes;

		$this->view->childrenIDs = $this->category->getChildrenIDs(true);

		if($this->view->childrenIDs[0] == '184'){
			$this->view->childrenIDs= array(
					'184',
					'186',
					'187'
			);

		}


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


/**
 * Рекламные объявления
 */

		$rolist = ORM::factory('ro')->find_in_category($this->category->id, TRUE);
		if(count($rolist)):
			$this->view->rolist = new View('b_ro_main_view');
			$this->view->rolist->list = $rolist;
		endif;

	}




	public function get($parent_id = 0){

		$obj = new Category_Model($parent_id);

		$sublist = $obj->where('status','enabled')->getSubList();

		if(count($sublist)):
			$this->data = array('subs' => array());
			foreach($sublist as $item):
				$this->data['subs'][] = array('id'=>$item->id, 'title'=>$item->stitle, 'has_children'=> $item->has_children, 'has_subway' => (int) $item->has_subway, 'has_district' => (int) $item->has_district);
			endforeach;
		else:

			$autotitles = array();
			if($obj->autotitle):
				$autotitles = $obj->title_formats->as_id_array('type_id');
				if(!empty($autotitles[0]->format)) $this->data['autotitle'] = 1;
			endif;

			if(count($obj->types)):
				if(empty($this->data)) $this->data = array();
				$this->data['types'] = array();
				foreach($obj->types as $item):
					$this->data['types'][] = array(
						'id'=>$item->id,
						'title'=>$item->intention_title,
						'has_price' => $item->has_price,
						'autotitle' => !empty($autotitles[$item->id]->format)
					);
				endforeach;
			endif;

			if(count($obj->properties)):
				if(empty($this->data)) $this->data = array();
				$this->data['properties'] = array();

				foreach($obj->properties as $item):

					$this->data['properties'][] = array('id'=>$item->id, 'title'=>$item->title, 'req' => $item->required, 'input' => $item->formField());

				endforeach;
			endif;

			if($obj->has_subway): $this->data['has_subway'] = $obj->has_subway; endif;
			if($obj->has_district): $this->data['has_district'] = $obj->has_district; endif;

		endif;

        //$this->data['category_warning'] = $obj->check_offers_limit($this->user);

		if(!request::is_ajax()){
			print_r($this->data);
			exit;
		}

	}
}