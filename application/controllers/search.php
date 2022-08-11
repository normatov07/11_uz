<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Search Controller.
 */
class Search_Controller extends Controller {

	public function __construct(){

		parent::__construct();

		if(!request::is_ajax()):
			$this->view = new View('search_view');
			$this->template->titleInView = true;

			$this->title = 'Поиск объявлений';

			$this->addJs('search.js');
		endif;

		$this->perpage = 30;

	}

	public function index2($page = 0){

/**
 * получаем список категорий и создаем списки корневых категорий для быстрой навигации и формы
 */
        $categories = ORM::factory('category')->find_all_enabled_cached();

		$qncategories = $maincategories = $categoryIDs = array();

		foreach($categories as $category):
			if($category->level == 1):
				$qncategories[$category->codename] =
				$maincategories[$category->id] = $category->title;
			endif;
			if(!empty($_REQUEST['category_id'])):
				if($_REQUEST['category_id'] == $category->id):
					$currentCategoryLeftKey = $category->left_key;
					$currentCategoryRightKey = $category->right_key;
					$categoryIDs[] = $category->id;
				elseif(!empty($currentCategoryLeftKey) and !empty($currentCategoryRightKey)
					and $category->left_key > $currentCategoryLeftKey
					and $category->right_key < $currentCategoryRightKey
					):
					$categoryIDs[] = $category->id;
				endif;
			endif;
		endforeach;

		$this->view->categories = $categories;
		$this->view->qncategories = $qncategories;
		$this->view->maincategories = $maincategories;

		$this->view->types = array(''=>'любой') + ORM::factory('type')->find_all()->select_list(NULL, 'title');

		if(!empty($_GET)):

			$filters = $_REQUEST;

			unset($filters['category_id']);

			$filters['q'] = htmlspecialchars(mb_substr(trim(@$_GET['q']), 0, 45, 'UTF-8'), ENT_COMPAT, 'UTF-8');

			if(!empty($filters['q'])):

				$this->title = 'Поиск: "'.$filters['q'].'"';
				$this->view->q = $filters['q'];
/* Вытаскиваем список регионов */
				$this->view->regions = ORM::factory('region')->find_all()->select_list();

				$offers = ORM::factory('offer');

				$this->view->SEARCH_PROCESSED = true;

				if($this->view->offersCount = $offers->setFilters($filters)->count_all_In_Category($categoryIDs)):

					$paginationConfig = array(
						'total_items'    => @$this->view->offersCount, // use db count query here of course
						//'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
						'items_per_page' => 70, // it may be handy to set defaults for stuff like this in config/pagination.php
						'item_title'		=> array('объявление','объявления', 'объявлений'),
					);

					$pagination =  new Pagination($paginationConfig);

					$this->view->offerList = $offers->setFilters($filters)->find_all_In_Category($categoryIDs, $this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);

					$this->view->pagination = $pagination;

					$this->template->yandex_direct = 3;

				endif;

			else:
				$this->errors->add('Вы не указали Ключевое слово или фразу');
			endif; // !empty keyword
		endif; // !empty($_GET)
	}

	public function index($page = 0){
		$page = intVal($page, 10);




/**
 * получаем список категорий и создаем списки корневых категорий для быстрой навигации и формы
 */
        $categories = ORM::factory('category')->find_all_enabled_cached();

		$qncategories = $maincategories = $categoryIDs = array();

		foreach($categories as $category):
			if($category->level == 1):
				$qncategories[$category->codename] =
				$maincategories[$category->id] = $category->title;
			endif;
			if(!empty($_REQUEST['category_id'])):
				if($_REQUEST['category_id'] == $category->id):
					$currentCategoryLeftKey = $category->left_key;
					$currentCategoryRightKey = $category->right_key;
					$categoryIDs[] = $category->id;
				elseif(!empty($currentCategoryLeftKey) and !empty($currentCategoryRightKey)
					and $category->left_key > $currentCategoryLeftKey
					and $category->right_key < $currentCategoryRightKey
					):
					$categoryIDs[] = $category->id;
				endif;
			endif;
		endforeach;

		$this->view->categories = $categories;
		$this->view->qncategories = $qncategories;
		$this->view->maincategories = $maincategories;

		$this->view->types = array(''=>'любой') + ORM::factory('type')->find_all()->select_list(NULL, 'title');

		if (!empty($_GET)):

			$filters = $_REQUEST;

			unset($filters['q']);
			foreach ($filters as $key=>$val):
				if (!in_array($key, array('category_id', 'period', 'type_id'))
					|| empty($val)):
					unset($filters[$key]);
				endif;
			endforeach;

			if (!empty($categoryIDs)) $filters['category_id'] = $categoryIDs;
			$search_str = htmlspecialchars(mb_substr(trim(@$_GET['q']), 0, 45, 'UTF-8'), ENT_COMPAT, 'UTF-8');

			$search = new search;

			if (!empty($search_str)):

				$found_data = array();
				//$found_data = $search->search($search_str, $filters, NULL, $this->perpage, $this->perpage*($page-1));
				$found_data = $search->search($search_str, $filters, NULL, 70,70*($page-1));

				$found_data['matches'] = isset($found_data['matches'])?$found_data['matches']:array();
				$found_data['total'] = isset($found_data['total'])?$found_data['total']:0;
				$found_ids = array_keys($found_data['matches']);

				$this->title = 'Поиск: "'.$search_str.'"';
				$this->view->q = $search_str;
				/* Вытаскиваем список регионов */
				$this->view->regions = ORM::factory('region')->find_all()->select_list();

				$this->view->SEARCH_PROCESSED = true;

				$found_offers = array();
				if (!empty($found_ids)):

					$offers = ORM::factory('offer');

					$this->view->offerList = $offers->find_by_ids($found_ids);

					$this->view->offersCount = $found_data['total'];

					$paginationConfig = array(
						'total_items'    => @$this->view->offersCount, // use db count query here of course
						//'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
						'items_per_page' => 70, // it may be handy to set defaults for stuff like this in config/pagination.php
						'item_title'		=> array('объявление','объявления', 'объявлений'),
					);


					$pagination =  new Pagination($paginationConfig);
					$this->view->pagination = $pagination;

					$this->template->yandex_direct = 3;
				endif;
			else:

				$this->errors->add('Вы не указали Ключевое слово или фразу');
			endif;
		endif;
	}
}
/* ?> */
