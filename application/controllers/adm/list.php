<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Lists Controller.
 */
class List_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();
		
		$this->view = new View('adm/list_view');
		$this->parent_title = 'Каталог';
		$this->title = 'Cписки-фильтры';
		
		$this->model = new List_Model;
	
		$this->addJs('adm_list.js');
		$this->addJs('listtable.js');
	}
	
	public function unique_title(Validation $v)
	{

		if(empty($v->title)) return true;	
		$this->model->select('id');
		$id = $this->model->find(array('title' => $v->title))->id;

		if($id == 0 or (!empty($v->id) and $v->id == $id)) return true;		
		
		$v->add_error('title', 'already_exists');
		
		return false;
	}
	
	
	public function check_items(Validation $v)
	{
		$invalid = array();
		$notempty = false;
		
		if(count($v->item)):
			foreach($v->item as $key => $item):			
				if(!empty($item['title'])) $notempty = true;
				if(!valid::standard_text($item['title']) or !valid::standard_text($item['valuedata'])):
//					$invalid[] = $key+1;
				endif;
			endforeach;
			
			if(empty($notempty)):
				$v->add_error('item', 'required');
			endif;
			if(count($invalid)):
				$v->add_error('item', 'invalid');
			endif;
		else:
			$v->add_error('item', 'required');
		endif;
		
		return false;
	}
	
		
	public function index($id = NULL){
	
		if (! empty($_POST)){
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST['delete'])):
				if (!empty($_POST['id'])):
					$this->obj = new $this->model($_POST['id']);
					if($this->obj->delete()):
						$this->messages->add('Список успешно удалён');
						unset($this->obj);
					endif;
				endif;				
			
			else:
			
				$_POST = new Validation($_POST);
				
				//add rules, filters
				$_POST->pre_filter('trim',true)
					  ->add_rules('title','required','length[2,64]')
					  ->add_callbacks('title', array($this, 'unique_title'))
					  ->add_rules('listtype','required')
					  ->add_callbacks('item', array($this, 'check_items'))
					  ->post_filter('utf8::ucfirst','title')	
				;
	
				$is_valid = $_POST->validate();
				$this->obj = new $this->model(@$_POST['id']);
				
				$this->obj->title = $_POST['title'];
				$this->obj->listtype = $_POST['listtype'];
				$this->obj->isfilter = !empty($_POST['isfilter'])?$_POST['isfilter']:NULL;
				$this->obj->isquick = !empty($_POST['isquick'])?$_POST['isquick']:NULL;
				$this->obj->ismultiple = !empty($_POST['ismultiple'])?$_POST['ismultiple']:NULL;
				
				$this->obj->default_empty = !empty($_POST['default_empty'])?(!empty($_POST['default_empty_title'])?$_POST['default_empty_title']:1):NULL;
				$this->obj->has_other = !empty($_POST['has_other'])?(!empty($_POST['has_other_title'])?$_POST['has_other_title']:1):NULL;
				
				if(!empty($_POST['item'])):
					
					$this->obj->items = array();
					
					$i = 0;
					foreach($_POST['item'] as $key => $itemData):
						if(empty($itemData['title'])) continue;
						$this->obj->items[$i]->id = @$itemData['id'];
						$this->obj->items[$i]->title = $itemData['title'];
						$this->obj->items[$i]->valuedata = !empty($itemData['valuedata'])?$itemData['valuedata']:$itemData['title'];
						if(!empty($_POST['default_item'])):
							if($_POST['default_item'] == $key):
								$this->obj->items[$i]->isdefault = 1;
							endif;
//						elseif($i == 0):
//							$this->obj->items[$i]->isdefault = 1;
						else:
							$this->obj->items[$i]->isdefault = 0;
						endif;
						$this->obj->items[$i]->priority = $i;
						$i++;
					endforeach;
					
				endif;
				
				if ($is_valid){				
					if($this->obj->save_with_related()):
						$this->messages->add('Список успешно сохранён');
						unset($this->obj);
					endif;
					
				}else{
					$this->errors->add($_POST->list_errors());
				}
			
			endif;
			
		}elseif(!empty($id)){
		
			$this->obj = new $this->model($id);
			$this->obj->items = $this->obj->list_items;
			
		}	

		$this->view->objList = $this->model->find_all();

	}
	
}
/* ?> */