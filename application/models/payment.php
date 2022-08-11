<?php defined('SYSPATH') or die('No direct script access.');

class Payment_Model extends ORM {
	
	protected $sorting = array('added' => 'desc');
	
	protected $belongs_to = array('user');
	
	protected $has_and_belongs_to_many = array('offers');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	
		if(empty($id)):
			$this->added = date::getForDb();
		endif;
	}
	
	public function __get($column){
	
		switch((string) $column):
		
			case 'url_proceed':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/my/payment/proceed/'.$this->id.'/';
				}
				return $this->object[$column];
			break;
			case 'url_cancel':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/my/payment/cancel/'.$this->id.'/';
				}
				return $this->object[$column];
			break;
			case 'url_confirm':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/my/payment/confirm/'.$this->method.'/';
				}
				return $this->object[$column];
			break;
			case 'url_success':
				if (!isset($this->object[$column])) {
					$this->object[$column] = '/my/payment/success/'.$this->id.'/';
				}
				return $this->object[$column];
			break;
			
		endswitch;
	
		return parent::__get($column);
	
	}
	
	public function save(){
		$this->updated = date::getForDb();
		return parent::save();
	}
	

	public function markExpired($user_id = NULL){
		$this->db->from($this->table_name);
		if($user_id != NULL) $this->db->where('user_id',$user_id);

		return
			$this->db->set('status','expired')
			->set('updated', date::getForDb())
			->where('status','ordered')
			->where('added < ', date::getForDb(strtotime('-'.Lib::config('payment.days_to_expiration').' days')))
			->update();
		
	}
	
	public function deleteExpired($user_id = NULL){
		
		if($user_id != NULL) $this->where('user_id',$user_id);

		return	$this->where('status','expired')
				->where('added < ', date::getForDb(strtotime('-'.Lib::config('payment.days_to_delete_expired').' days')))
				->delete_all();
		
	}
	
	public function setStatus($status){	

		if(!in_array($status, array_keys(Lib::config('payment.status')))) return false;
		
		if($this->status == $status) return true;
		
		switch($status):
	
			case 'complete':				
			
				switch($this->service):
					case 'bonus':
					
						if(!$this->user->account->user_id):
							$this->user->account->setUpdated();
							$this->user->account->user_id = $this->user_id;
						endif;
		
						$this->user->account->bonuses += $this->units_bought;
						$this->user->account->total_bonuses += $this->units_bought;
							
						if($this->user->account->save()):
						
							if(Lib::config('payment.method', $this->method, 'notification') and $this->user->email != ''):
							
								$message_data = array(
								
									'email' => $this->user->email,
									'bonuses' => $this->units_bought,
									'payment_details' => $this->details,
									'total' => $this->user->account->bonuses,
		
								);
						
								$message_tpl = 'adm/user/email/bonus_added_view';
								$subject = 'Вам добавлены бонусы!';
				
								Lib::sendEmail($subject, $message_tpl, $message_data);		
							endif;
						
						endif;
					
					break;
					case 'position':
					case 'premium':
					case 'mark':
						
						if($this->method == 'bonus'):
						
							if($this->user->account->bonuses < $this->price) return false;
							$this->user->account->bonuses -= $this->price;
							if(!$this->user->account->save()): return false; endif;
						
						endif;						
						
						$amount = Lib::config('payment.service', $this->service, 'amount');
						$price = ORM::factory('tariff')->where('service=', $this->service)->where('method=', 'bonus')->find()->price;
						
						foreach($this->offers as $item):
							
							switch($this->service):
								case 'premium':
									$item->setPremium(intVal($this->units_bought));
								break;
								case 'mark':
									$item->setMarked(intVal($this->units_bought));
								break;
								case 'position':
									$item->setPosition();
								break;
							endswitch;
							
						endforeach;
					
					break;
					
				endswitch;
				
				$this->completed = date::getForDb();
			break;
			case 'expired':
			case 'ordered':
			case 'cancelled':
			
				if($this->status == 'complete'):
				
					if($this->method == 'bonus'):
						
						$this->user->account->bonuses += $this->price;
						if(!$this->user->account->save()) return false;
					
					endif;
				
					switch($this->service):
						case 'bonus':
						
							if(!$this->user->account->user_id):
								$this->user->account->setUpdated();
								$this->user->account->user_id = $this->obj->id;
							else:
								$this->user->account->bonuses = ($this->user->account->bonuses > $this->units_bought ? $this->user->account->bonuses - $this->units_bought : 0);
								$this->user->account->total_bonuses = ($this->user->account->total_bonuses > $this->units_bought ? $this->user->account->total_bonuses - $this->units_bought : 0);
							endif;
								
							$this->user->account->save();
							
						break;
						case 'position':
						case 'premium':
						case 'mark':
							
							foreach($this->offers as $item):
								
								switch($this->service):
									case 'premium':
										$item->unsetPremium();
									break;
									case 'mark':
										$item->unssetMarked();
									break;
									case 'position':
	//									$item->setPosition();
									break;
								endswitch;
								
							endforeach;
						
						break;
						
					endswitch;	
					
				endif; // this status = complete
				
			break;
		endswitch;
				
		$this->status = $status;
		return $this->save();
		
	}

}
/* ?> */