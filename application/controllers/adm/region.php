<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * regions Controller.
 */
class Region_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();
		
		$this->view = new View('adm/region_view');
		$this->addJs('listtable.js');
		
		$this->parent_title = 'Настройки';
		$this->title = 'Cписок регионов';
		
		$this->objecttitle = 'Регион';
		
		$this->model = new Region_Model;

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
						$this->messages->add($this->objecttitle . ' успешно удалён');
						unset($this->obj);
					endif;
				endif;				
			
			else:
			
				$_POST = new Validation($_POST);
				
				$_POST['codename'] = (!empty($_POST['codename']))?url::title($_POST['codename']):url::title($_POST['title']);
				
				//add rules, filters
				$_POST->pre_filter('trim',true)
					  ->add_rules('title','required','length[3,64]')
					  ->add_rules('codename', 'length[3,64]')
					  ->add_rules('priority', 'length[0,3]')
					  ->add_callbacks('title', array($this, 'unique_title'))
					  ->post_filter('utf8::ucfirst','title')	
				;
	
				$is_valid = $_POST->validate();
				
				$this->obj = new $this->model(@$_POST['id']);				
				
				
							
				$this->obj->setValuesFromArray($_POST);	
								
				if ($is_valid){				
				
					if($this->obj->save()):
					
						$currentDistricts = $this->obj->districts->as_id_array();

						if(!empty($_POST->district) and !empty($_POST->district_type)):
							foreach($_POST->district as $item):
								if(!empty($item['title'])):
									if(!isset($currentDistricts[$item['id']])):
										$currentDistricts[$item['id']] = new District_Model();
										$currentDistricts[$item['id']]->{$this->obj->foreign_key()} = $this->obj->id;
									endif;
									
									$currentDistricts[$item['id']]->title = $item['title'];
									$currentDistricts[$item['id']]->save();
									unset($currentDistricts[$item['id']]);
								endif;
							endforeach;
						endif;
						
						foreach($currentDistricts as $item):
							$item->delete();
						endforeach;
						
						$currentSubways = $this->obj->subways->as_id_array();

						if(!empty($_POST->subway) and !empty($_POST->has_subway)):
							foreach($_POST->subway as $item):
								if(!empty($item['title'])):
									if(!isset($currentSubways[$item['id']])):
										$currentSubways[$item['id']] = new Subway_Model();
										$currentSubways[$item['id']]->{$this->obj->foreign_key()} = $this->obj->id;
									endif;
									
									$currentSubways[$item['id']]->title = $item['title'];
									$currentSubways[$item['id']]->save();
									unset($currentSubways[$item['id']]);
								endif;
							endforeach;
						endif;
						
						foreach($currentSubways as $item):
							$item->delete();
						endforeach;
					
						$this->messages->add($this->objecttitle . ' успешно сохранён');
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