<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * RO Controller.
 */
class Ro_Controller extends AdmController {
	
	public $EDITMODE;
	
	public function __construct(){
	
		parent::__construct();
		
		$this->parent_title = 'Рекламные объявления';
		if(!$this->isAdministrator()) return Lib::pagenotfound();	
		
		$this->clients_perpage = 20;
		
	}
	
	public function index($page = 0){
	
		$this->perpage = 30;
		
		$this->view = new View('adm/ro/ro_view');
		$this->title = 'Менеджер';
		$this->addJs('adm_ro.js');
		
		$this->state = 'current';
		
		if(!empty($_GET)):
		
			if(!empty($_GET['category_id'])) $this->category_id = $_GET['category_id'];
			if(!empty($_GET['state'])) $this->state = $_GET['state'];
			
			if(!empty($_GET['q'])) $this->q = trim($_GET['q']);
			if(!empty($_GET['subject'])) $this->subject = $_GET['subject'];
					
			$this->filterset = true;
			
		endif;				
						
		$this->model = ORM::factory('ro');		
		
		$this->setFilters();
		
		$modelCount = $this->model->count_all();
//echo $this->model->last_query();
		if(!empty($modelCount)):
			
			$paginationConfig = array(
				'total_items'    => $modelCount, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
				'item_title'		=> '',
			);
			
			$pagination = new Pagination($paginationConfig);
					
			$this->setFilters();
			
			$this->view->objectList = $this->model->find_all($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
			
			$this->view->picture = ORM::factory('picture')->in($this->model->foreign_key(), $this->view->objectList->getValues())->find_all()->as_id_array($this->model->foreign_key());
			
			$this->view->pagination = $pagination;
			
		endif;
		
		$this->view->categoryList = ORM::factory('category')->where('ro_price is not null')->find_all()->as_id_array();		
		
		$this->view->q = @$this->q;
		$this->view->subject = @$this->subject;
		$this->view->category_id = @$this->category_id;
		$this->view->state = @$this->state;
		$this->view->filterset = @$this->filterset;
	}
	
	private function setFilters(){

		if(!empty($this->category_id)):
		
			if($this->category_id == 'HOME'):
				$this->model->where('onhome', 1); 
			else:
				$this->model->join('categories_ros as cr', 'ros.id', 'cr.ro_id');
				$this->model->where('cr.category_id', $this->category_id); 
			endif;
			
		endif;
		
		switch(@$this->state):
			case 'current':
			
				$this->model->where('(date_end >= "' . date::getDateForDb() . '" OR status = "disabled")');
				
			break;
			case 'active':
			
				$this->model->where('status', 'enabled');
				$this->model->where('date_end >=', date::getDateForDb());
				
			break;
			case 'expired':
			
				$this->model->where('date_end <', date::getDateForDb());
				
			break;
			case 'expiring':
			
				$this->model->where('status', 'enabled');
				$this->model->where('date_end >=', date::getDateForDb());
				$this->model->orderby('date_end','asc');
				
			break;
		endswitch;
		
		if(!empty($this->q)):
			switch(@$this->subject):
				case 'client':					
					$this->model->like('ro_client_title',$this->q);
				break;
				case 'agent':					
					$this->model->like('ro_agent_title',$this->q);
				break;
				default:				
					$this->model->like('title',$this->q);
				break;
			endswitch;
		endif;
		
	}


	public function change_status($id = NULL, $status = NULL){
	
		if(!empty($id) and !empty($status) and in_array($status, array('enabled','disabled'))):
		
			$ro = ORM::factory('ro', $id);
			
			if($ro->id != 0):			
					

				if($ro->status != $status):
				
					$this->messages->add('Статус успешно сменён!');
				
					$ro->status = $status;
					$ro->save();
					
					$this->data['status'] = $ro->status;
					$this->data['state'] = $ro->state;
					$this->data['state_title'] = $ro->state_title;
					$this->data['act'] = 'change_status';					
		
				endif;
				
			endif;
		
		endif;
		
		if(!request::is_ajax()):
			return $this->index();
		endif;
	}
	
	public function delete($id = NULL){
		if(!empty($id)):
		
			$obj = ORM::factory('ro', $id);
			if($obj->id):			
							
				$obj->delete();
				$this->data['act'] = 'delete';
				
			endif;
		
		endif;
		if(!request::is_ajax()):
			$this->index();
		endif;
	}

	public function edit($id = NULL){
		
		$this->view = new View('adm/ro/ro_edit_view');

		$this->title = 'Создание рекламного блока';
		
		$this->addJs('adm_ro.js');
		
		$this->objecttitle = 'РО';
		
		$this->model = new RO_Model;
		
		$this->perpage = 20;

		if (! empty($_POST)){
		
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST['dublicate'])):
			
				$this->obj = new $this->model(@$_POST['id']);
				unset($this->obj->id);
			
			elseif(!empty($_POST['delete'])):
			
				if (!empty($_POST['id'])):
					$this->obj = new $this->model($_POST['id']);
					if($this->obj->delete()):				
						$this->messages->add('Рекламное объявление успешно удалено');
					endif;
				endif;				
			
			else:
					
				$_POST = new Validation(array_merge($_POST, $_FILES));
							
				$this->obj = new $this->model(@$_POST['id']);
				
				if(!empty($_POST['id'])) $this->EDITMODE = true;
				
				//add rules, filters
				$_POST->pre_filter('trim',true)
					->pre_filter('strip_tags', 'title', 'description')
					->add_rules('ro_client_title','length[2,50]')
					->add_rules('ro_agent_title','length[2,45]')
					->add_rules('ro_client_inn','length[2,45]')

					->add_rules('title','required','length[2,50]')
					->add_rules('description','length[0,110]')
//					->add_rules('description','required','length[2,110]')
					->add_rules('content','length[2,2000]')
					->add_rules('category_id','required')
					
					->add_rules('organization','length[2,128]')
					->add_rules('phone','length[7,128]')
					->add_rules('fax','length[7,128]')
					->add_rules('email','length[5,128]', array('valid','email'))
					->add_rules('website','length[5,255]', array('valid','url'))
					->add_rules('price','length[1,128]')
					->add_rules('cost','valid::numeric')
					
					->add_rules('start','required')
					->add_rules('end','required')
				;
				
				if(empty($_POST->redirect)):
					if(empty($_POST->content) and !empty($_POST->url)):
						$_POST->redirect = 'url';
					else:
						$_POST->redirect = 'content';
					endif;
				endif;
				
				if($_POST->redirect == 'url' and empty($_POST->website)):
				
					$_POST->add_error('url','required');
					
				endif;
				
				if(!$this->obj->id and empty($_POST->image['name'][0])):
				
					$_POST->add_error('image','required');
					
				endif;

			
				if(empty($_POST->category_id) or !count($_POST->category_id)):
					$this->errors->add('Не выбран раздел');
					
				else:
					$isnotempty = false;
					foreach($_POST->category_id as $cat):
						if($cat) $isnotempty = true;
					endforeach;
					if(!$isnotempty):
							$this->errors->add('Не выбран раздел');
					endif;
				endif;

				
				if(!empty($_POST->ro_client_id)):
					$client = ORM::factory('ro_client', $_POST->ro_client_id);
				elseif(!empty($_POST->ro_client_inn)):
					$client = ORM::factory('ro_client')->find(array('inn' => $_POST->ro_client_inn));
				elseif(!empty($_POST->ro_client_title)):
					$client = ORM::factory('ro_client')->find(array('title' => $_POST->ro_client_title));
				endif;
				
				if(empty($client->id) and empty($_POST->ro_client_title)):
					$_POST->add_error('ro_client_title', 'required');
				endif;
				
				$is_valid = $_POST->validate();
								
				$_POST->title = text::typographyString($_POST->title);
				$_POST->description = text::typographyString($_POST->description);
				$_POST->content = text::wordwrap_decorate_urls(text::typography($_POST->content));

	
				if(!empty($_POST->status)) $_POST->status = 'enabled';
				else $_POST->status = 'disabled';

				$this->obj->setValuesFromArray($_POST);				
				
				if ($is_valid){		

/**
 * привязываем или создаем клиента и агента
 */						
					
					if(empty($client)) $client = new Ro_Client_Model;
					
					if(empty($client->id)):
						$client->title = $_POST->ro_client_title;
						$client->inn = @$_POST->ro_client_inn;
						$client->status = 'enabled';
						$client->save();	
					else:	
						$this->obj->ro_client_title = $client->title;			
					endif;
					
					$this->obj->{$client->foreign_key()} = $client->id;
					
					if(!empty($_POST->ro_agent_id)):
						$agent = ORM::factory('ro_agent', $_POST->ro_agent_id);
					elseif(!empty($_POST->ro_agent_title)):
						$agent = new Ro_Agent_Model;
					endif;
					
					if(!empty($agent)):
						if(empty($agent->id)):
							$agent->title = $_POST->ro_agent_title;
							$agent->status = 'enabled';
							$agent->save();
						endif;
						$this->obj->{$agent->foreign_key()} = $agent->id;
						$this->obj->ro_agent_title = $agent->title;
					endif;



/**
 * Привязываем разделы
 */
					
					if(in_array('HOME', $_POST->category_id)):
						$this->obj->onhome = 1;
					else:
						$this->obj->onhome = 0;
					endif;	

					if($this->obj->id != 0):
						$currentCats = $this->obj->categories->as_id_array();
					endif;


					foreach($_POST->category_id as $category_id):
					
						if($category_id == 'HOME'):
							continue;
						endif;

						if(!empty($currentCats[$category_id])):
							unset($currentCats[$category_id]);
							continue;
						endif;
						
						$category = ORM::factory('category', $category_id);
						
						if($category->id != 0):
							$this->obj->add($category);
						endif;
					
					endforeach;	
				
					if(!empty($currentCats) and count($currentCats)):
						foreach($currentCats as $curcat):	
							$this->obj->remove($curcat);
						endforeach;
					endif;

/**
 * Выставляем дату
 */
					
					$this->obj->date_start = date::getDateForDb($_POST->start);
					$this->obj->date_end = date::getDateForDb($_POST->end);
				

/**
 * Сохраняем
 */

			
					$this->obj->save();
				
/**
 * IMAGES
 */						
					$imagesAmount = 0;
					$imagecount = 1;

					if(!isset($_POST->mainimage)) $_POST->mainimage = 0;
				
					if($imagesAmount < Lib::config('picture.ro', 'max_amount') and count(Lib::config('picture.ro', 'folder')) and !empty($_POST->image) and !empty($_POST->image['name']) and !empty($_POST->image['name'][0])):

						if($this->obj->id and $this->obj->pictures->count()):
							foreach($this->obj->pictures as $pic):
								$pic->delete();
							endforeach;
						endif;
			
						$imageError = array();
						
						foreach($_POST->image['name'] as $key => $value):
							
							if(empty($value)) continue;
							if($imagecount > Lib::config('picture.ro', 'max_amount')) break;
							
							$imagetitle = '&laquo;Изображение №'.($key+1).'&raquo;';								
							
							if(!$error = valid::image('ro', $_POST->image, $key, $imagetitle)):

								$picture = new Picture_Model;
								$picture->mode = 'ro';
															
								$picture->{$this->obj->foreign_key()} = $this->obj->id;
								
								if(@$_POST->mainimage == ($key + $imagesAmount)):
									$picture->priority = 0;
								else:
									$picture->priority = $imagecount++;
								endif;
								
								$picture->new_file = $_POST->image['tmp_name'][$key];
								
								if(!$picture->save()):
									$imageError[] = 'Не удалось сохранить ' . $imagetitle;
								//else:
								//	$newimagehtml = $picture->f('thumb','html');
								endif;
							else:
								$imageError[] = $error;
							endif;
							
						endforeach;

						if(count($imageError)):
							$this->errors->add(join('<br>', $imageError).($this->EDITMODE?'':'<br>Вы сможете исправить это в режиме редактирования.'));
						endif;
						
						$importantContentChanged = true;
						
					endif;				
					
					$this->messages->add('Рекламное объявление успешно сохранено');
					
					if(!empty($_POST->save)):
						
						if(request::is_ajax()):		
						
							$this->data['save'] = true;	
							$this->data['id'] = $this->obj->id;				
							if(!empty($newimagehtml)) $this->data['pic'] = $newimagehtml;
							unset($this->obj);
							
						endif;
						
					else:
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
		
		$this->view->categoryList = ORM::factory('category')->where('ro_price is not null')->find_all();
		
		if(!empty($this->obj)) $this->obj->content = text::undecorate_urls($this->obj->content);
		
		$this->view->clientList = ORM::factory('ro_client')->where('status','enabled')->find_all()->select_list();
		$this->view->agentList = ORM::factory('ro_agent')->where('status','enabled')->find_all()->select_list();

	}
	
	public function unique(Validation $v, $var = 'title'){
		if(empty($v->$var)) return true;	
		
		$return = false;
		
		$this->model->select('id');
		$id = $this->model->find(array($var => $v->$var))->id;
		
		if($id == 0 or (!empty($v->id) and $v->id == $id)) $return = true;		
		else $v->add_error($var, 'already_exists');
		
		return $return;
	}
	
	public function unique_inn(Validation $v){
		return $this->unique($v, 'inn');
	}
	
	public function unique_title(Validation $v){
		return $this->unique($v, 'title');
	}
	
	public function client($id = NULL, $page = 0){
	
		$this->model = new Ro_client_Model;		

		if (! empty($_POST)){
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST['delete'])):
			
				if (!empty($_POST['id'])):
					$this->obj = new $this->model($_POST['id']);
					if($this->obj->delete()):				
						$this->messages->add('Клиент успешно удалён');
					endif;
				endif;				
			
			else:
			
				$_POST = new Validation($_POST);
				
				//add rules, filters
				$_POST->pre_filter('trim',true)
					->add_rules('title','required','length[2,50]')
					->add_rules('inn','length[2,45]')
					->add_callbacks('title', array($this, 'unique_title'))
					->add_callbacks('inn', array($this, 'unique_inn'))
					->post_filter('utf8::ucfirst','title')	
				
				;
	
				$is_valid = $_POST->validate();
				$this->obj = new $this->model(@$_POST['id']);
				
				$this->obj->title = $_POST['title'];
				$this->obj->inn = @$_POST['inn'];
				$this->obj->status = !empty($_POST['status'])?'enabled':'disabled';
				
				if ($is_valid){				
				
					if($this->obj->save()):
						$this->messages->add('Клиент успешно сохранён');
						unset($this->obj);
					endif;
					
				}else{
					$this->errors->add($_POST->list_errors());
				}
			
			endif;
			
		}elseif(!empty($id)){
		
			$this->obj = new $this->model($id);
			
		}	
		$this->view = new View('/adm/ro/client_view');
		$this->title = 'Клиенты';
//		$this->view->objList = $this->model->find_all();
		
		
		
		$this->view->mode = '';
		
		if(!empty($_REQUEST['q'])):
			$this->view->q = $_REQUEST['q'];
		elseif(!empty($_REQUEST['mode'])):
			$this->view->mode = $_REQUEST['mode'];
		else:
			$this->view->mode = 'enabled';
		endif;	

		$this->view->list_page = $page;	
	
		$this->setClientFilters(true);
	
		$countTotal = $this->model->count_all();
		
		$paginationConfig = array(
			'total_items'    => $countTotal, // use db count query here of course
			'items_per_page' => $this->clients_perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
			'item_title'		=> array('клиент','клиента', 'клиентов'),
		);
		
		$pagination = new Pagination($paginationConfig);
			
		$this->view->pagination = @$pagination;
	
		$this->setClientFilters();
		
		$this->view->objList = $this->model->find_all($this->clients_perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->clients_perpage);
		
		
		

	}
	
	public function agent($id = NULL, $page = 0){
	
		$this->model = new Ro_agent_Model;		

		if (! empty($_POST)){
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST['delete'])):
			
				if (!empty($_POST['id'])):
					$this->obj = new $this->model($_POST['id']);
					if($this->obj->delete()):				
						$this->messages->add('Клиент успешно удалён');
					endif;
				endif;				
			
			else:
			
				$_POST = new Validation($_POST);
				
				//add rules, filters
				$_POST->pre_filter('trim',true)
					->add_rules('title','required','length[2,45]')
					->add_callbacks('title', array($this, 'unique'))
					->post_filter('utf8::ucfirst','title')	
				
				;
	
				$is_valid = $_POST->validate();
				$this->obj = new $this->model(@$_POST['id']);
				
				$this->obj->title = $_POST['title'];
//				$this->obj->inn = @$_POST['inn'];
				$this->obj->status = !empty($_POST['status'])?'enabled':'disabled';
				
				if ($is_valid){				
				
					if($this->obj->save()):
						$this->messages->add('Агент успешно сохранён');
						unset($this->obj);
					endif;
					
				}else{
					$this->errors->add($_POST->list_errors());
				}
			
			endif;
			
		}elseif(!empty($id)){
		
			$this->obj = new $this->model($id);
			
		}	
		$this->view = new View('/adm/ro/agent_view');
		$this->title = 'Агенты';
			
		//$this->view->objList = $this->model->find_all();
		
		$this->view->mode = '';
		
		if(!empty($_REQUEST['q'])):
			$this->view->q = $_REQUEST['q'];
		elseif(!empty($_REQUEST['mode'])):
			$this->view->mode = $_REQUEST['mode'];
		else:
			$this->view->mode = 'enabled';
		endif;	

		$this->view->list_page = $page;	
	
		$this->setClientFilters(true);
	
		$countTotal = $this->model->count_all();
		
		$paginationConfig = array(
			'total_items'    => $countTotal, // use db count query here of course
			'items_per_page' => $this->clients_perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
			'item_title'		=> array('агент','агента', 'агентов'),
		);
		
		$pagination = new Pagination($paginationConfig);
	
		
		$this->view->pagination = @$pagination;
			

		$this->setClientFilters();
		
		$this->view->objList = $this->model->find_all($this->clients_perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->clients_perpage);
		
	}
	
	private function setClientFilters($forcount = FALSE){

		if(!empty($this->view->q)):
			$this->model->orlike(array('title' => $this->view->q));
		elseif(!empty($this->view->mode)):
			switch($this->view->mode):
				case 'enabled':
				case 'disabled':
				default:
					$this->model->where('status',$this->view->mode?$this->view->mode:'enabled');
				break;
			endswitch;
		endif;
	}
	
	
	function statistics($mode = NULL){
	
		$this->view = new View('/adm/ro/statistics_view');
		
		if(empty($_GET['year'])) $year = (int) date::getYear();
		else $year = (int) $_GET['year'];
		
		if(empty($_GET['month'])) $month = (int) date::getMonth();
		else $month = (int) $_GET['month'];
		
		$datemask = $year . ($month ? '-' . ($month<10?'0':''). $month :'');
		
		$datemaskfull = $datemask .'-01';
		$nextmonthdatemask = date::getDateForDb($datemaskfull . '+ 1 month');
		
		switch($mode):
			case 'agents':
				$this->title = 'Отчёт по агентам';
				
				$this->title = 'Финансовый отчёт';
				$rolist = ORM::factory('ro')
					->select('ros.id, ros.date_start, ros.ro_client_id, cr.category_id, ros.cost, ra.title as agent_title, ros.ro_agent_id')
					->join('categories_ros as cr', 'cr.ro_id', 'ros.id', 'left')
					->join('ro_agents as ra', 'ra.id', 'ros.ro_agent_id', 'left')
					->where('DATE_FORMAT(ros.date_start,"%Y-%m") = "' . $datemask .'"')
					->where('ros.status = "enabled"')
					->find_all();
				
				$data = array();
					
				foreach($rolist as $item):
	
					if(!isset($data[$item->ro_agent_id])):
						$data[$item->ro_agent_id] = array();
						$data[$item->ro_agent_id]['title'] = $item->ro_agent_id?$item->agent_title:Lib::config('app.title');
					endif;
	
					if(!isset($data[$item->ro_agent_id]['ro'][$item->id])):
						@$data[$item->ro_agent_id]['ro'][$item->id] = 1;
						@$data[$item->ro_agent_id]['income'] += $item->cost;
					endif;
					
					$data[$item->ro_agent_id]['category'][$item->category_id] = 1;
					$data[$item->ro_agent_id]['client'][$item->ro_client_id] = 1;
					
				endforeach;
							
				$this->view->data = $data;
				
				
			break;
			case 'finanse':
				$this->title = 'Финансовый отчёт';
				$rolist = ORM::factory('ro')
					->select('ros.id, ros.date_start, ros.ro_client_id, cr.category_id, ros.cost')
					->join('categories_ros as cr', 'cr.ro_id', 'ros.id', 'left')
					->where('DATE_FORMAT(ros.date_start,"%Y") = "' . $year .'"')
					->where('ros.status = "enabled"')
					->find_all();
				
				$data = array();
					
				foreach($rolist as $item):
	
					$item_datemask = date::getYear($item->date_start). '-' .date::getMonth($item->date_start);
					$item_month = (int) date::getMonth($item->date_start);
				
					if(!isset($data[$item_month]['ro'][$item->id])):
						@$data[$item_month]['ro'][$item->id] = 1;
						@$data[$item_month]['income'] += $item->cost;
						if($item->cost) @$data[$item_month]['paid_ro'][$item->id] = 1;
					endif;
					
					$data[$item_month]['category'][$item->category_id] = 1;
					$data[$item_month]['client'][$item->ro_client_id] = 1;
					
				endforeach;
							
				$clientlist = ORM::factory('ro_client')->where('DATE_FORMAT(added,"%Y") = "' . $year .'"')
					->find_all();
				
				foreach($clientlist as $item):
					$item_month = (int) date::getMonth($item->added);
					if(!isset($data[$item_month]['new_client'][$item->id])):
						$data[$item_month]['new_client'][$item->id] = 1;
					endif;
				endforeach;
				
				
				$this->view->data = $data;
			break;
			case 'monthly':
				$this->title = 'Статистика по месяцам';
				
				$periodselect = '(DATE_FORMAT(ros.date_start,"%Y-%m") = "' . $datemask .'" or DATE_FORMAT(ros.date_end,"%Y-%m") = "' . $datemask .'")';
				
				$rosinperiod = ORM::factory('ro')
					->select('ros.id, ros.status, ros.date_start, ros.date_end, ros.cost, ros.onhome, cr.category_id, cat.parent_id, cat.title as cat_title')
					->join('categories_ros as cr', 'cr.ro_id', 'ros.id', 'left')
					->join('categories as cat', 'cr.category_id', 'cat.id', 'left')
					->where($periodselect)
					->where('ros.status','enabled')
					->orderby('cat.left_key, ros.id')
					->find_all();
				
				
				
				$categoryStat = array();
				$parents = array();
				$total = array();
				
				foreach($rosinperiod as $item):
				
					if($item->onhome):
						@$categoryStat[0][$item->state][$item->id] = 1;
						@$categoryStat[0]['title'] = 'Главная';
						if(empty($item->cost)):
							@$categoryStat[0]['free'][$item->id] = 1;
						endif;
					endif;	
					
					if($item->category_id):						
						@$categoryStat[$item->category_id][$item->state][$item->id] = 1;
						$categoryStat[$item->category_id]['title'] = $item->cat_title;
						if($item->parent_id):
							$parents[$item->parent_id] = 1;
							$categoryStat[$item->category_id]['parent'] = $item->parent_id;
						endif;

						if($item->date_start > $datemaskfull and $item->date_start < $nextmonthdatemask):
							@$categoryStat[$item->category_id]['new'][$item->id] = 1;
						endif;
						
						if(empty($item->cost)):
							@$categoryStat[$item->category_id]['free'][$item->id] = 1;
						endif;
					endif;
					
					$total[$item->state][$item->id] = 1;
					
					if($item->date_start > $datemaskfull and $item->date_start < $nextmonthdatemask):
						@$total['new'][$item->id] = 1;
					endif;				
					if(empty($item->cost)):
						@$total['free'][$item->id] = 1;
					endif;
					
				endforeach;

				if(!empty($parents) and count($parents)):
					$this->view->categoryparents = ORM::factory('category')->select('id, title')->in('id', array_keys($parents))->find_all()->as_id_array();
				endif;
				
				$this->view->total = $total;
				ksort($categoryStat);
				$this->view->categoryStat = $categoryStat;
				
			break;
			default:
				$this->title = 'Текущая статистика';
				
				$periodselect = '(ros.date_start >= DATE(NOW()) or ros.date_end >= DATE(NOW()))';
				
				$rosinperiod = ORM::factory('ro')
					->select('ros.id, ros.status, ros.date_start, ros.date_end, ros.cost, ros.onhome, cr.category_id, cat.parent_id, cat.title as cat_title')
					->join('categories_ros as cr', 'cr.ro_id', 'ros.id', 'left')
					->join('categories as cat', 'cr.category_id', 'cat.id', 'left')
					->where($periodselect)
					->where('ros.status','enabled')
					->orderby('cat.left_key, ros.id')
					->find_all();
				
				
				
				$categoryStat = array();
				$parents = array();
				$total = array();
				
				foreach($rosinperiod as $item):
				
					if($item->onhome):
						@$categoryStat[0][$item->state][$item->id] = 1;
						@$categoryStat[0]['title'] = 'Главная';
						if(empty($item->cost)):
							@$categoryStat[0]['free'][$item->id] = 1;
						endif;
					endif;	
					
					if($item->category_id):						
						@$categoryStat[$item->category_id][$item->state][$item->id] = 1;
						$categoryStat[$item->category_id]['title'] = $item->cat_title;
						
						if($item->parent_id):
							$parents[$item->parent_id] = 1;
							$categoryStat[$item->category_id]['parent'] = $item->parent_id;
						endif;

						if($item->date_start > $datemaskfull and $item->date_start < $nextmonthdatemask):
							@$categoryStat[$item->category_id]['new'][$item->id] = 1;
						endif;
						
						if(empty($item->cost)):
							@$categoryStat[$item->category_id]['free'][$item->id] = 1;
						else:
							@$categoryStat[$item->category_id]['paid'][$item->id] = 1;
						endif;
					endif;
					
					$total[$item->state][$item->id] = 1;
					
					if($item->date_start > $datemaskfull and $item->date_start < $nextmonthdatemask):
						@$total['new'][$item->id] = 1;
					endif;				
					if(empty($item->cost)):
						@$total['free'][$item->id] = 1;
					endif;
					
				endforeach;

				if(!empty($parents) and count($parents)):
					$this->view->categoryparents = ORM::factory('category')->select('id, title')->in('id', array_keys($parents))->find_all()->as_id_array();
				endif;
				
				$this->view->total = $total;
				ksort($categoryStat);
				$this->view->categoryStat = $categoryStat;
				
				
// OVERALL		
				$rolist = ORM::factory('ro')->find_all();
		
				foreach($rolist as $item):
					$overall[$item->state][$item->id] = 1;
					if(empty($item->cost)):
						@$overall['free_'.$item->state][$item->id] = 1;
					else:
						@$overall['paid_'.$item->state][$item->id] = 1;
					endif;
				endforeach;
				
				$this->view->overall = $overall;
				
			break;
		endswitch;
		
		$this->view->pageid = $mode;
		$this->view->year = $year;
		$this->view->month = $month;
		$this->view->months = array_slice(date::$months, 1, NULL, true);
	
	}
	
}
/* ?> */