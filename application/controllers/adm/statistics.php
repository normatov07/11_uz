<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Controller.
 */
class Statistics_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();
		
		$this->parent_title = '';
		$this->title = 'Статистика';
		$this->view = new View('adm/statistics_view');
		
	}
	
		
	public function index(){
		
		$this->view->offers_total = ORM::factory('offer')->count_all();
	
		$this->view->enabled_offers_total = ORM::factory('offer')->count_all_in_Category(0);
				
		$today = date::getForDb('today');
		$yesterday = date::getForDb('yesterday');
		
		$this->view->offers_today = ORM::factory('offer')->where('added >= ', $today)->count_all_in_Category(0);
		$this->view->offers_today_disabled = ORM::factory('offer')->where('added >= ', $today)->where('status', 'disabled')->count_all();
		$this->view->offers_yesterday = ORM::factory('offer')->where('added >= ', $yesterday)->where('added < ', $today)->count_all_in_Category(0);
		$this->view->offers_yesterday_disabled = ORM::factory('offer')->where('added >= ', $yesterday)->where('added < ', $today)->where('status', 'disabled')->count_all();
		
		$this_month = date::getForDb(date('Y-m-').'01');
		$last_month = date::getForDb(strtotime(date('Y-m-d', mktime (0,0,0,date('m')-1,1))));
		
		$this->view->offers_this_month = ORM::factory('offer')->where('added >= ', $this_month)->count_all();
		$this->view->offers_last_month = ORM::factory('offer')->where('added >= ', $last_month)->where('added < ', $this_month)->count_all();
				
		$this->view->messages_today = ORM::factory('message')->where('added >= ', $today)->count_all();
		$this->view->messages_yesterday = ORM::factory('message')->where('added >= ', $yesterday)->where('added < ', $today)->count_all();
		
		$this->view->users_today = ORM::factory('user')->where('registered >= ', $today)->where('status','enabled')->count_all();
		$this->view->users_today_disabled = ORM::factory('user')->where('registered >= ', $today)->where('status','disabled')->count_all();
		$this->view->users_yesterday = ORM::factory('user')->where('registered >= ', $yesterday)->where('registered < ', $today)->where('status','enabled')->count_all();
		$this->view->users_yesterday_disabled = ORM::factory('user')->where('registered >= ', $yesterday)->where('status','disabled')->count_all();

		$this->view->users_total = ORM::factory('user')->where('status','enabled')->count_all();
		$this->view->users_this_month = ORM::factory('user')->where('registered >= ', $this_month)->where('status','enabled')->count_all();
		$this->view->users_last_month = ORM::factory('user')->where('registered >= ', $last_month)->where('registered < ', $this_month)->where('status','enabled')->count_all();
		
		$this->view->types = ORM::factory('type')->find_all();
		$this->view->offers_by_types = ORM::factory('offer')->select('type_id')->select('count(*) as counted')->groupby('type_id')->where('status', 'enabled')->find_all_in_Category(0)->as_id_array('type_id');
		
		$this->view->regions = ORM::factory('region')->find_all()->select_list();
		$this->view->offers_by_regions = ORM::factory('offer')->select('region_id')->select('count(*) as counted')->groupby('region_id')->where('status', 'enabled')->orderby('counted','desc')->find_all_in_Category(0)->as_id_array('region_id');
		
		$this->view->p_categories = ORM::factory('offer')->select('count(*) as records_found')->select('category_id')->groupby('category_id')->orderby('records_found', 'desc')->limit(12)->find_all_in_Category(0)->select_list('category_id','records_found');
		$this->view->categories = ORM::factory('category')->in('id', array_keys($this->view->p_categories))->find_all()->select_list();
		
	}
	
}
/* ?> */