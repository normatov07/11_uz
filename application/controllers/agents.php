<?php defined('SYSPATH') or die('No direct script access.');

class Agents_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->view = new View('agents_view');
		$this->title = 'Рекламным агентам';

	}
	

	
	public function index()
	{
		
	}

}