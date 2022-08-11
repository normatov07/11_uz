<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Ro view controller
 */
class Ro_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
		
//		$this->noindex = true;
		
	}

	public function index($obj_id = NULL)
	{
	
		if($obj_id == NULL) return Lib::pagenotfound();
		
		$this->obj = new Ro_Model($obj_id);
		
		if($this->obj->id == 0) return Lib::pagenotfound();
		
		if(!$this->isModerator()):
			$this->obj->clicks++;
			$this->obj->save();
		endif;
		
		if($this->obj->redirect == 'url'):
			if(empty($this->obj->website)) return Lib::pagenotfound();
			$this->redirect = $this->obj->website;
			return;
		endif;
		
		$this->view = new View('ro_view');
		$this->parent_title = 'Рекламное объявление';
		$this->title = $this->obj->title;
		
		$this->view->obj = $this->obj;
		
		
	}


		
		
	
}
/* ?> */