<?php defined('SYSPATH') or die('No direct script access.');

class Help_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->view = new View('help_view');
		$this->title = 'Помощь';

	}
	

	
	public function index($item_id = NULL)
	{
		
	}
	
	public function premium($item_id = NULL)
	{
		
	}
}