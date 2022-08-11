<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Tariffs Controller.
 */
class Tariff_Controller extends AdmController {
	
	public function __construct(){
	
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();
		
		$this->view = new View('adm/tariff_view');
		$this->parent_title = 'Настройки';
		$this->title = 'Управление ценами';
		$this->addJs('adm_tariff.js');
		
		$this->model = new Tariff_Model;

	}
	
	
	public function index($id = NULL){
		
		if (! empty($_POST)){
						
				$_POST = new Validation($_POST);

				$tariff = array();
				
				foreach($_POST->id as $service => $methods):
					$tariff[$service] = array();
					
					foreach($methods as $method => $id):
					
						$has_error = false;
					
						if(empty($id) or !$tariff[$service][$method] = new $this->model($id)):
						
							$tariff[$service][$method] = new $this->model();

						endif;	
						
						$price_title = Lib::config('payment.service',$service, 'title') . ' - ' . Lib::config('payment.method',$method, 'title');

						if(empty($_POST->price[$service][$method])):
							$this->errors->add('Цена обязательна: ' . $price_title);
							$has_error = true;
						elseif(!is_numeric($_POST->price[$service][$method]) or (Lib::config('payment.method', $method, 'currency') == 'bonus' and !is_integer($_POST->price[$service][$method]*1))):
							$this->errors->add('Неверный формат цены: ' . $price_title);
							$has_error = true;
						endif;
						
/*						if(!empty($_POST->amount[$service][$method]) and !is_numeric($_POST->amount[$service][$method])):
							$this->errors->add('Неверное количество: ' . $price_title);
							$has_error = true;
						endif;										
*/
						$tariff[$service][$method]->service = $service;
						$tariff[$service][$method]->method = $method;
//						$tariff[$service][$method]->amount = @$_POST->amount[$service][$method];
						$tariff[$service][$method]->price = @$_POST->price[$service][$method];
						$tariff[$service][$method]->status = @$_POST->status[$service][$method];
						$tariff[$service][$method]->comment = @$_POST->comment[$service][$method];
						
						if(!$has_error) $tariff[$service][$method]->save();

					endforeach;
				endforeach;

				if (!$has_error){				
				
					$this->messages->add('Тарифы успешно сохранены');
						
				}
			
		
		}	
		
		if(empty($tariff)):
		
			$objList = $this->model->find_all();
			foreach($objList as $item):
				$tariff[$item->service][$item->method] = $item;
			endforeach;
		endif;
		
		$this->view->tariff = @$tariff;
	
	
	}
	
}
/* ?> */