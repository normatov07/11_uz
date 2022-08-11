<?php defined('SYSPATH') or die('No direct script access.');

class Access_Denied_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->view = new View('s_access_denied_view');
		$this->title = 'Доступ запрещён';

	}
	

	
	public function index($item_id = NULL)
	{
		
	}
	

}