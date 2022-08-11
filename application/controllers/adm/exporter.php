<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Exporter Controller.
 */
class Exporter_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();
		
		$this->view = new View('adm/exporter_view');
		$this->title = 'Экспортёры';
		
		$this->objecttitle = 'Экспортёр';
		
		$this->model = new Exporter_Model;
		
		$this->perpage = 5;
	}
	
	
	public function unique_login(Validation $v){
		if(empty($v->login)) return true;	
		
		$return = false;
		
		$this->model->select('id');
		$id = $this->model->find(array('login' => $v->login))->id;
		
		if($id == 0 or (!empty($v->id) and $v->id == $id)) $return = true;		
		else $v->add_error('login', 'already_exists');
		
		$this->model->select('id');
		$id = $this->model->find(array('title' => $v->title))->id;
		if($id == 0 or (!empty($v->id) and $v->id == $id)) $return?true:false;		
		else $v->add_error('title', 'already_exists');
		
		return $return;
	}


	public function index($id = NULL, $page = 0){
	
	
	
		if (! empty($_POST)){
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST['delete'])):
				if (!empty($_POST['id'])):
					$this->obj = new $this->model($_POST['id']);
					if($this->obj->delete()):				
						$this->messages->add('Экспортёр успешно удален');
					endif;
				endif;				
			
			else:
			
				$_POST = new Validation($_POST);
				
				//add rules, filters
				$_POST->pre_filter('trim',true)
					  ->add_rules('login','required','length[2,45]')
					  ->add_rules('password','required','length[2,45]')
					  ->add_rules('title','required','length[2,45]')
					  ->add_callbacks('login', array($this, 'unique_login'));
	
				$is_valid = $_POST->validate();
				
				$this->obj = new $this->model(@$_POST['id']);
				
				$_POST->status = !empty($_POST->status)?'enabled':'disabled';	
				
				$this->obj->setValuesFromArray($_POST);				
				
				if ($is_valid){				
				
					if($this->obj->save()):
						$this->messages->add('Экспортёр успешно сохранен');
						if(empty($_POST->save)) unset($this->obj);
					endif;
					
				}else{
					$this->errors->add($_POST->list_errors());
					$this->view->obj = $this->obj;
				}
			
			endif;
			
		}elseif(!empty($id)){
			$this->obj = new $this->model($id);			
		}
	
		
		$objectTotal = $this->model->count_all();
		
		$paginationConfig = array(
			'total_items'    => $objectTotal, // use db count query here of course
			'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
			'item_title'		=> array('экспортёр','экспортёра', 'экспортёров'),
		);
		
		$pagination = new Pagination($paginationConfig);
			
		$this->view->objectList = $this->model->find_all($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
	
		$this->view->pagination = $pagination;
	}
	
}
/* ?> */