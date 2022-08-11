<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Category Controller.
 */
class Category_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();
		
		$this->view = new View('adm/category_view');
		$this->parent_title = 'Каталог';
		$this->title = 'Разделы';
		
//		$this->modelName = 'Category_Model';
		$this->model = new Category_Model;
//		$this->obj = new $this->modelName();	
		
//		$this->addJs('adm_category.js');
		$this->addJs('tree.js');
		$this->addJs('listtable.js');
		
		$this->lists = new List_Model();
		$this->view->lists = $this->lists->get_titles_list();
	}
	
	public function unique_title(Validation $v){
		if(empty($v->title)) return true;	
		
		$return = false;
		
		$this->model->select('id, parent_id');
		$present = $this->model->find(array('title' => $v->title));
		
		if(empty($present->id) 
			or @$v->parent_id != @$present->parent_id 
			or (!empty($v->id) 
				and $v->id == $present->id 
				and @$v->parent_id == @$present->parent_id)
				) $return = true;
		else $v->add_error('title', 'already_exists');
		
		$this->model->select('id');
		$id = $this->model->find(array('codename' => $v->codename))->id;
		if($id == 0 or (!empty($v->id) and $v->id == $id)) $return?true:false;		
		else $v->add_error('codename', 'already_exists');
		
		return $return;
	}
	
	
	public function check_items(Validation $v)
	{
		$invalid = array();
		$notempty = false;
		
		if($this->obj->has_children) return true;
		
		if(count($v->item)):
			foreach($v->item as $key => $item):			
				if(!empty($item['title'])) $notempty = true;
/*				
				if(!valid::standard_text($item['title']) or !valid::standard_text($item['units'])):
					$invalid[] = $key+1;
				endif;
//*/				
			endforeach;
			
/*			
			if(empty($notempty)):
				$v->add_error('item', 'recquired');
			endif;
//*/			
			if(count($invalid)):
				$v->add_error('property', 'invalid');
			endif;
			
/*		else:
			$v->add_error('item', 'required');
*/			
		endif;
		
		return false;
	}
	
		
	public function check_types(Validation $v)
	{
		$invalid = array();
		$notempty = false;
		
//		if($this->obj->has_children) return true;
		
		if(!empty($v->type_id) and count($v->type_id)):
		
			foreach($v->type_id as $key => $type_id):			
				if(!empty($type_id)) $notempty = true;
			endforeach;
			
			if(empty($notempty)):
				$v->add_error('type_id', 'required');
			endif;
		
		else:
			$v->add_error('type_id', 'Тип объявлений обязательный параметр!');
		endif;
		
		return false;
	}
	

	public function check_title_format(Validation $v)
	{
		if(!empty($v->type_autoformat) and count($v->type_autoformat)){
			foreach($v->type_autoformat as $i => $format){
				if(preg_match_all('/\{/i', $format, $match) != preg_match_all('/\}/i', $format, $match)){
					$v->add_error('type_autoformat', 'Неверный формат автозаголовка №' . ($i+1));
				}
			}
		}
	}
	
	public function index($id = NULL){
	
		if (! empty($_POST)){
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST['delete'])):
				if (!empty($_POST['id'])):
					$this->obj = new $this->model($_POST['id']);
					if($this->obj->delete()):
						$this->messages->add('Раздел успешно удалён');
						unset($this->obj);
					endif;
				endif;				
			
			else:
		
				$_POST = new Validation($_POST);
				
				$_POST['codename'] = (!empty($_POST['codename']))?url::title($_POST['codename']):url::title($_POST['title']);
				
				$this->obj = new $this->model(@$_POST['id']);
				
				//add rules, filters
				$_POST->pre_filter('trim',true)
					->add_rules('title','required','length[2,64]')
					->add_rules('short_title','length[2,64]')
					->add_rules('codename', 'length[2,64]')
					->add_rules('priority', array('valid','digit'))
//					->add_rules('type_id','required')
					->add_rules('description','length[2,255]')
					->add_callbacks('title', array($this, 'unique_title'))
					->add_callbacks('item', array($this, 'check_items'))
					->add_callbacks('type_id', array($this, 'check_types'))
					->add_callbacks('type_autoformat', array($this, 'check_title_format'))
					->post_filter('utf8::ucfirst','title', 'short_title','description')
					->add_rules('ro_price', array('valid','numeric'))
					;
				
				$is_valid = $_POST->validate();
				
				$this->obj->title = $_POST['title'];
				$this->obj->short_title = $_POST['short_title'];
				$this->obj->codename = $_POST['codename'];

				$this->obj->new_priority = isset($_POST['priority'])?$_POST['priority']:NULL;
				$this->obj->status = !empty($_POST['status'])?'enabled':'disabled';
				$this->obj->parent_id = !empty($_POST['parent_id'])?$_POST['parent_id']:0;
				$this->obj->description = $_POST['description'];
				$this->obj->type_id = @$_POST['type_id'];
				$this->obj->type_autoformat = @$_POST['type_autoformat'];
				$this->obj->autotitle = @$_POST['autotitle'];
				
				$this->obj->has_district = @$_POST['has_district'];
				$this->obj->has_subway = @$_POST['has_subway'];
				$this->obj->ro_price = @$_POST['ro_price'];
				$this->obj->color = @$_POST['color'];
				
				if(!empty($_POST['item'])):
					$i = 0;
					foreach($_POST['item'] as $itemData):
						if(empty($itemData['title'])) continue;
						$this->obj->items[$i]->id = @$itemData['id'];
						$this->obj->items[$i]->title = $itemData['title'];
						$this->obj->items[$i]->codename = $itemData['codename'];
						$this->obj->items[$i]->datatype = $itemData['datatype'];
						$this->obj->items[$i]->units = $itemData['units'];						
						$this->obj->items[$i]->minlength = $itemData['minlength'];
						$this->obj->items[$i]->maxlength = $itemData['maxlength'];
						$this->obj->items[$i]->required = !empty($itemData['required'])?$itemData['required']:0;
						$this->obj->items[$i]->list_id = (!empty($itemData['islist']) and !empty($itemData['list_id']))?$itemData['list_id']:NULL;
						$this->obj->items[$i]->isquicklist = (!empty($itemData['list_id']) and !empty($itemData['isquicklist']))?$itemData['isquicklist']:0;
						$this->obj->items[$i]->priority = $i;						
						$i++;
					endforeach;
				endif;
	
				if($is_valid):

					if($this->obj->save_with_related()):
					
						$this->messages->add('Список успешно сохранён');
						if(!empty($_POST['add'])) unset($this->obj);
						
					endif;
					
				else:
				
					$this->errors->add($_POST->list_errors());
					
				endif;
			
			endif;
			
		}elseif(!empty($id)){
		
			$this->obj = new $this->model($id);
			$this->obj->items = $this->obj->properties;
		}
		
//		if(!empty($this->obj) and empty($this->obj->parentObj)) $this->obj->getParent();

		$this->view->objList = $this->model->find_all();

	}


}
/* ?> */