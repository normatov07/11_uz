<?php defined('SYSPATH') or die('No direct script access.');
/**
 * offerer controller
 */
class Offerer_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
			
		$this->perpage = 30;
		
		$this->noindex = true;
		
	}

	public function index($offerer_id = NULL, $page = 0)
	{
		if($offerer_id == NULL)  return Lib::pagenotfound();
		
		$this->offerer = new User_Model($offerer_id);
		
		if($this->offerer->id == 0)  return Lib::pagenotfound();
		
		$this->view = new View('offerer_view');
		$this->title = 'Профиль автора: ' . $this->offerer->public_name;
		
		$this->view->offerer = $this->offerer;
		
		$this->template->titleInView = true;
		
		$where = array(
			'user_id' => $this->offerer->id,
			'status' => 'enabled',
		);
		
		if(!$this->offerer->is_agent or !$this->isModerator()):
			$where['has_not_user'] = 0;
		endif;
		
		if($offersCount = ORM::factory('offer')->where($where)->count_all()):
		
			$paginationConfig = array(
				'total_items'    => $offersCount, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
//				'item_title'		=> array('объявление','объявления', 'объявлений'),
//				'group' => 'fe_list',
			);
			
			$pagination = new Pagination($paginationConfig);
									
			$this->view->offerList = ORM::factory('offer')->where($where)->find_all($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
			$this->view->pagination = $pagination;
			
			$this->view->categories = ORM::factory('category')->in('id', $this->view->offerList->getValues('category_id'))->find_all()->as_id_array();
				
			$this->view->types = ORM::factory('type')->find_all()->select_list(NULL, 'intention_title');
			$this->view->regions = ORM::factory('region')->find_all()->select_list();
			
			$offerIDs = array_keys($this->view->offerList->select_list());
			
			if(count($offerIDs)) $this->view->pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);
				
		endif;
		
	}


		
		
	
}
/* ?> */