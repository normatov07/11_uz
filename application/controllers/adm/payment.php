<?php defined('SYSPATH') or die('No direct script access.');

class Payment_Controller extends AdmController {

	public function __construct()
	{
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();

	}
	
	public function index($page = 0){
		$this->perpage = 200;
		
		$this->view = new View('adm/payment_view');
//		$this->parent_title = 'Настройки';
		$this->title = 'Менеджер платежей';
		$this->addJs('adm_payment.js');
		
		if(!empty($_GET)):
		
			if((int) @$_GET['date']['year']):
				$this->date = (int) $_GET['date']['year'];
				if((int) @$_GET['date']['month']):
				
					$this->date .= '-';
					if((int) $_GET['date']['month'] < 10) $this->date .= '0';
					$this->date .= (int) $_GET['date']['month'];
					
					if((int) @$_GET['date']['day']):
						$this->date .= '-';
						if((int) $_GET['date']['day'] < 10) $this->date .= '0';
						$this->date .= (int) $_GET['date']['day'];
					endif;
				endif;
			endif;
			
			if(!empty($_GET['service_id'])) $this->service_id = $_GET['service_id'];
			if(!empty($_GET['status'])) $this->status = $_GET['status'];
			
			if(!empty($_GET['q'])) $this->q = trim($_GET['q']);
			if(!empty($_GET['subject'])) $this->subject = $_GET['subject'];
					
			$this->filterset = true;
			
		endif;				
						
		$this->payments = ORM::factory('payment');		
		
/**
 * Помечаем просроченные платежи
 */
		$this->payments->markExpired($this->user->id);
		
		
		$this->setFilters();
		
		$paymentsCount = $this->payments->count_all();
		
		if(!empty($paymentsCount)):
			
			$paginationConfig = array(
				'total_items'    => $paymentsCount, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
				'item_title'		=> '',
			);
			
			$pagination = new Pagination($paginationConfig);
		
			$this->setFilters();
			
			$this->view->paymentList = $this->payments->find_all($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
			
			$this->view->pagination = $pagination;
			
			$this->view->users = ORM::factory('user')->in('id', $this->view->paymentList->getValues('user_id'))->find_all()->as_id_array();
			
		endif;
		
		$this->view->q = @$this->q;
		$this->view->subject = @$this->subject;
		$this->view->date = @$this->date;
		$this->view->service_id = @$this->service_id;
		$this->view->status = @$this->status;
		$this->view->filterset = @$this->filterset;
		
	}
	
	private function setFilters(){
//		$this->payments->where('user_id', $this->user->id);
		if(!empty($this->date)):
			$this->payments->like('added', $this->date); 
		endif;
		if(!empty($this->service_id)):
			$this->payments->where('service', $this->service_id); 
		endif;
		if(!empty($this->status)):
			$this->payments->where('status', $this->status); 
		endif;
		
		if(!empty($this->q)):
			switch(@$this->subject):
				case 'user_id':					
					$this->payments->where('user_id',$this->q);
				break;
				case 'merchant_transaction':					
					$this->payments->where('ps_transaction_id',$this->q);
				break;
				default:				
					$this->payments->where('id',$this->q);
				break;
			endswitch;
		endif;
		
	}


	public function change_status(){
		if(!empty($_POST)):
		
			if(empty($_POST['id'])) return;
			
			if(!in_array(@$_POST['status'], array_keys(Lib::config('payment.status')))) return;
			
			$payment = ORM::factory('payment', $_POST['id']);
			
			if($payment->id == 0) return;
			
			if($payment->setStatus($_POST['status'])):
			
				$this->data['status'] = $_POST['status'];
				$this->data['status_title'] = Lib::config('payment.status', $_POST['status']);
				
			endif;
		
		endif;
	}

}