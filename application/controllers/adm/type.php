<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Offers Type Controller.
 */
class Type_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();
		
		$this->view = new View('adm/type_view');
		$this->parent_title = 'Настройки';
		$this->title = 'Типы объявлений';
		
		$this->model = new Type_Model;

	}
	
	public function unique_title(Validation $v){
		if(empty($v->title)) return true;	
		
		$return = false;
		
		$this->model->select('id');
		$id = $this->model->find(array('title' => $v->title))->id;
		
		if($id == 0 or (!empty($v->id) and $v->id == $id)) $return = true;		
		else $v->add_error('title', 'already_exists');
		
		$this->model->select('id');
		$id = $this->model->find(array('codename' => $v->codename))->id;
		if($id == 0 or (!empty($v->id) and $v->id == $id)) $return?true:false;		
		else $v->add_error('codename', 'already_exists');
		
		return $return;
	}
	
	
		
	public function index($id = NULL){
	
		if (! empty($_POST)){
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST['delete'])):
			
				if (!empty($_POST['id'])):
					$this->obj = new $this->model($_POST['id']);
					if($this->obj->delete()):				
						$this->messages->add('Тип объявлений успешно удалён');
					endif;
				endif;				
			
			else:
			
				$_POST = new Validation($_POST);
				
				$_POST['codename'] = (!empty($_POST['codename']))?url::title($_POST['codename']):url::title($_POST['title']);
								
				//add rules, filters
				$_POST->pre_filter('trim',true)
					  ->add_rules('title','required','length[2,64]')
					  ->add_rules('other_title','length[2,64]')
					  ->add_rules('codename', 'length[2,64]')
					  ->add_rules('priority', 'length[0,3]')
					  ->add_callbacks('title', array($this, 'unique_title'))
					  ->post_filter('utf8::ucfirst','title','other_title')	
					  
					;
	
				$is_valid = $_POST->validate();
				$this->obj = new $this->model(@$_POST['id']);
				
				$this->obj->title = $_POST['title'];
				$this->obj->other_title = !empty($_POST['other_title'])?$_POST['other_title']:$_POST['title'];
				$this->obj->intention_title = !empty($_POST['intention_title'])?$_POST['intention_title']:$_POST['title'];
				$this->obj->codename = $_POST['codename'];
				$this->obj->priority = $_POST['priority'];
				$this->obj->on_home = !empty($_POST['on_home'])?$_POST['on_home']:NULL;
				$this->obj->has_price = !empty($_POST['has_price'])?$_POST['has_price']:NULL;
				
				if ($is_valid){				
				
					if($this->obj->save()):
						$this->messages->add('Тип объявлений успешно сохранён');
						unset($this->obj);
					endif;
					
				}else{
					$this->errors->add($_POST->list_errors());
					$this->view->obj = $this->obj;
				}
			
			endif;
			
		}elseif(!empty($id)){
		
			$this->obj = new $this->model($id);
			
		}	
		
		$this->view->objList = $this->model->find_all();

	}
	
}
/* ?> */