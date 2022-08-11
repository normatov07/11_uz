<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Bonus Controller.
 */
class Exchange_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		$this->view = new View('adm/exchange_view');
		$this->parent_title = 'Настройки';
		$this->title = 'Курсы валют';
//		$this->addJs('adm_bonus.js');
		$this->model = new Exchange_Model;
		
		$this->objecttitle = 'Курс валют';
	}
	
	public function correct_date(Validation $v){
	
		if(empty($v->date)) return true;
		
		if(!checkdate((int) $v->date['month'], (int) $v->date['day'], (int) $v->date['year'])):
			$v->add_error('date', 'Неверная "date"!');
			return false;
		endif;
		
		$v->date = sprintf('%04d-%02d-%02d', $v->date['year'], $v->date['month'], $v->date['day']);
				
		return true;
	}
	
	
	public function index(){
		
		if(!empty($_POST)):
			
			$_POST = new Validation($_POST);
			
			$_POST->pre_filter('trim',true)
					->add_rules('date','required')
					->add_callbacks('date', array($this, 'correct_date'))
			;
			
			if(!empty($_POST['new'])):
			
			elseif(!empty($_POST->delete)):

				if($_POST->validate()):
				
					$this->obj = $this->model->find(array('added' => $_POST->date));
					
					if($this->obj->delete()):				
						$this->messages->add($this->objecttitle . ' успешно удалён');
						unset($this->obj);
					endif;
					
				else:
					$this->errors->add($_POST->list_errors());
				endif;				
			
			else:							
				
				$_POST->usd = preg_replace('/,/','.',$_POST->usd);
				$_POST->eur = preg_replace('/,/','.',$_POST->eur);
				$_POST->rub = preg_replace('/,/','.',$_POST->rub);
				
				//add rules, filters
				$_POST					
					->add_rules('usd', 'required', array('valid','numeric'))
					->add_rules('eur', 'required', array('valid','numeric'))
					->add_rules('rub', 'required', array('valid','numeric'))
				;
	
				$is_valid = $_POST->validate();
				
				$this->obj = $this->model->find(array('added' => $_POST['date']));	

				$this->obj->setValuesFromArray($_POST);	
				$this->obj->added = $_POST->date;
				
				if ($is_valid){				
				
					if($this->obj->save()):
						$this->messages->add($this->objecttitle . ' успешно сохранён');
						unset($this->obj);
					endif;
					
				}else{
					$this->errors->add($_POST->list_errors());
					$this->view->obj = $this->obj;
				}
			
			endif;
			
			
		elseif(!empty($_GET['d'])):
			$this->obj = $this->model->find(array('added' => $_GET['d']));	
			
		endif;
	
		if(!request::is_ajax()):
			$this->view->objList = $this->model->limit(5)->find_all();
			$this->view->todayExchange = ORM::factory('exchange')->getCurrent();
			if(empty($_POST) and empty($this->obj) and $this->view->todayExchange->added == date::getDateForDb()) $this->view->obj = $this->view->todayExchange;
		endif;
		

	}
	
}
/* ?> */