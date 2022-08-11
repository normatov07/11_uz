<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * News Controller.
 */
class News_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();
		
		$this->view = new View('adm/news_view');
//		$this->parent_title = 'Настройки';
		$this->title = 'Новости';
		
		$this->objecttitle = 'Новость';
		
		$this->model = new News_Entry_Model;
		
		$this->perpage = 5;
	}
	
	public function correct_date(Validation $v){
	
		if(empty($v->publication)) return true;
		
		if(!checkdate((int) $v->publication['month'], (int) $v->publication['day'], (int) $v->publication['year']) or empty($v->publication['hour'])  or empty($v->publication['minute']) or $v->publication['hour'] > 23 or $v->publication['minute'] > 59):
			$v->add_error('publication', 'Неверная "publication"!');
			return false;
		endif;
		
		$v->publication = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $v->publication['year'], $v->publication['month'], $v->publication['day'], $v->publication['hour'], $v->publication['minute'], '00');
				
		return true;
	}
	
	public function index($id = NULL, $page = 0){
	
	
	
		if (! empty($_POST)){
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST['delete'])):
				if (!empty($_POST['id'])):
					$this->obj = new $this->model($_POST['id']);
					if($this->obj->delete()):				
						$this->messages->add('Новость успешно удалена');
					endif;
				endif;				
			
			else:
			
				$_POST = new Validation($_POST);
				
				$_POST['codename'] = (!empty($_POST['codename']))?url::title($_POST['codename']):url::title($_POST['title']);
				
				//add rules, filters
				$_POST->pre_filter('trim',true)
					  ->pre_filter('utf8::ucfirst','title', 'description', 'content')
					  ->add_rules('title','required','length[2,128]')
					  ->add_rules('description','required','length[2,255]')
					  ->add_rules('content','required','length[2,2024]')
					  ->add_callbacks('publication', array($this, 'correct_date'));
	
				$is_valid = $_POST->validate();
				
				$this->obj = new $this->model(@$_POST['id']);
				
				$_POST->title = text::typographyString($_POST->title);
				$_POST->description = text::typographyString($_POST->description);
				$_POST->content = text::typography($_POST->content);
				
				if(!empty($_POST->edit_published)):
					$_POST->published = $_POST->publication;
				elseif(!empty($_POST->status) and empty($this->obj->published)):
					$_POST->published = date::getForDb();
				endif;
				
				$_POST->status = !empty($_POST->status)?'enabled':'disabled';	
				
				$this->obj->setValuesFromArray($_POST);				
				
				if ($is_valid){				
				
					if($this->obj->save()):
						$this->messages->add('Новость успешно сохранена');
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
	
		
		$publishedTotal = $this->model->countPublished();
		
		$paginationConfig = array(
			'total_items'    => $publishedTotal, // use db count query here of course
			'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
			'item_title'		=> array('новость','новости', 'новостей'),
		);
		
		$pagination = new Pagination($paginationConfig);
	
		$this->view->publishedList = $this->model->findPublished($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
		$this->view->readyList = $this->model->where(array('status' => 'enabled', 'published > ' => date::getForDb()))->find_all();
		$this->view->draftList = $this->model->where(array('status' => 'disabled'))->orderby('added','desc')->find_all();
		
		$this->view->pagination = $pagination;
	}
	
}
/* ?> */