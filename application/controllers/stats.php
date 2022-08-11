<?php defined('SYSPATH') or die('No direct script access.');

class Stats_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->view = new View('stats_view');
		$this->title = 'Статистика посещаемости сайта';

	}
	

	
	public function index($item_id = NULL)
	{



		header('Location: http://www.uz/ru/res/visitor/index?id=16378&Visitor%5Bfrom%5D=01.01.2016s&Visitor%5Bto%5D='.$date = date('d.m.Y').'&Visitor%5Btype%5D=visitors&Visitor%5Bperiod%5D=monthly');
		exit;
	}
	

}