<?php defined('SYSPATH') or die('No direct script access.');

class News_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->view = new View('news_view');
		$this->title = 'Новости';
		$this->perpage = 10;

	}
	

	
	public function index($id = NULL, $page = 0)
	{
		if(!empty($id)):
		
			$this->view->obj = new News_Entry_Model($id);
			
			if($this->view->obj->id == 0):
				return $this->pagenotfound();
			endif;
			$this->parent_title = 'Новость';
			$this->title = $this->view->obj->title;
			
		elseif($publishedTotal = ORM::factory('news_entry')->countPublished()):
		
			$paginationConfig = array(
				'total_items'    => $publishedTotal, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
//				'item_title'		=> array('новость','новости', 'новостей'),
				'group' => 'fe_list',
			);
			
			$pagination = new Pagination($paginationConfig);
				
			$this->view->publishedList = ORM::factory('news_entry')->findPublished($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
			
			$this->view->pagination = $pagination;
		
		
		endif;
		
	}
	

}