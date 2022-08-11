<?php defined('SYSPATH') or die('No direct script access.');

class PageNotFound_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->view = new View('s_404_view');
		$this->title = '404 Страница не найдена';

	}
	

	
	public function index($item_id = NULL)
	{
		
	}
	

}