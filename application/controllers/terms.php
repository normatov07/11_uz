<?php defined('SYSPATH') or die('No direct script access.');

class Terms_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->view = new View('terms_view');
		$this->title = 'Пользовательское соглашение';

	}
	

	
	public function index($item_id = NULL)
	{
		
	}
	

}