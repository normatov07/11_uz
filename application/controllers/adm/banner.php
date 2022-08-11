<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Bonus Controller.
 */
class Banner_Controller extends AdmController {

    public $banners;
	public function __construct(){
	
		parent::__construct();

        $this->parent_title = 'Баннеры';
        $this->title = 'Добавление баннера';

        echo time();
        exit;
        $this->view = new View('adm/banner_view');

    }
	
	
	public function index(){


	}
	
}