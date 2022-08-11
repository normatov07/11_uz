<?php defined('SYSPATH') or die('No direct script access.');

class Sms_Controller extends AdmController {

	public function __construct()
	{
		parent::__construct();
		
		if(!$this->isAdministrator()) return Lib::pagenotfound();

	}
	
	public function index($page = 0){
		$this->perpage = 200;
		
		
		$this->view = new View('adm/sms_view');
//		$this->parent_title = 'Настройки';
		$this->title = 'SMS-платежи';
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
						
		$this->sms_requests = ORM::factory('sms_request');		
		
		$this->setFilters();
		
		$sms_requestsCount = $this->sms_requests->count_all();
		
		if(!empty($sms_requestsCount)):
			
			$paginationConfig = array(
				'total_items'    => $sms_requestsCount, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
				'item_title'		=> '',
			);
			
			$pagination = new Pagination($paginationConfig);
		
			$this->setFilters();
			
			$this->view->requestList = $this->sms_requests->find_all($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
			
			$this->view->pagination = $pagination;
					
			if($offerIDs = $this->view->requestList->getValues('offer_id')):
				$offersList = ORM::factory('offer')->in('id', $offerIDs)->find_all();
				$this->view->offers = $offersList->as_id_array();
				
				if($userIDs = $offersList->getValues('user_id')):
					$this->view->users = ORM::factory('user')->in('id', $userIDs)->find_all()->as_id_array();
				endif;
			endif;
		endif;
		
		$this->view->q = @$this->q;
		$this->view->subject = @$this->subject;
		$this->view->date = @$this->date;
		$this->view->service_id = @$this->service_id;
		$this->view->status = @$this->status;
		$this->view->filterset = @$this->filterset;
		
	}
	
	private function setFilters(){
//		$this->sms_requests->where('user_id', $this->user->id);
		if(!empty($this->date)):
			$this->sms_requests->like('added', $this->date); 
		endif;
		if(!empty($this->service_id)):
			$this->sms_requests->where('service', $this->service_id); 
		endif;
		if(!empty($this->status)):
			$this->sms_requests->where('status', $this->status); 
		endif;
		
		if(!empty($this->q)):
			switch(@$this->subject):
				case 'user_id':					
					$this->sms_requests->where('user_id',$this->q);
				break;
				case 'transaction':					
					$this->sms_requests->where('transaction_id',$this->q);
				break;
				default:				
					$this->sms_requests->where('id',$this->q);
				break;
			endswitch;
		endif;
		
	}


	public function change_status(){
	
		if(!empty($_POST)):
		
			if(empty($_POST['id'])) return;
			
			if(!in_array(@$_POST['status'], array_keys(Lib::config('sms.status')))) return;
			
			$payment = ORM::factory('sms_request', $_POST['id']);
			
			if($payment->id == 0) return;
			
			if($payment->setStatus($_POST['status'])):
			
				$this->data['status'] = $_POST['status'];
				$this->data['status_title'] = Lib::config('sms.status', $_POST['status']);
				
			endif;
		
		endif;
	}

}