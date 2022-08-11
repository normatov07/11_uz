<?php defined('SYSPATH') or die('No direct script access.');

class Adv_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->view = new View('adv_view');
		$this->title = 'Услуги и реклама';

	}
	

	
	public function index($pageid = NULL)
	{
		switch(@$pageid):
			case 'ro':
				$this->parent_title = 'Рекламные объявления';
				$this->view->categories = ORM::factory('category')->where('ro_price !=','')->find_all_enabled();
			break;
			case 'banner':
				$this->parent_title = 'Баннерная реклама';
			break;
			case 'sms':
				$this->parent_title = 'SMS-услуги';
			break;
			default:
				$this->view->tariffs = ORM::factory('tariff')->where('status','enabled')->where('method','ekarmon')->find_all()->as_id_array('service');		
		endswitch;
		$this->view->pageid = $pageid;
	}
	

}