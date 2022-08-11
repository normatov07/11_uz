<?php defined('SYSPATH') or die('No direct script access.');

class SMS_Request_Model extends ORM {
	
	protected $belongs_to = array('user','offer');	
	
	protected $sorting = array('added' => 'desc');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	
		if(empty($id)):
			$this->added = date::getForDb();
		endif;
	}

	public function __get($column){
	
		switch((string) $column):
		
			case 'url_complete':
				if (empty($this->object[$column])) {
					$this->object[$column] ='/sms/complete/'. $this->id .'/';
				}
				return $this->object[$column];
			break;	
			
		endswitch;
	
		return parent::__get($column);
	
	}	

		
	public function setStatus($status){	

		if(!in_array($status, array_keys(Lib::config('sms.status')))) return false;
		
		if($this->status == $status) return true;
		
		switch($status):
	
			case 'complete':	
				$this->completed = date::getForDb();	
			break;		
			case 'replied':
				$this->replied = date::getForDb();
			break;
		endswitch;
		
		
		$this->status = $status;
		return $this->save();
		
	}


	public function sendReply($error = ''){	
		
		if(!empty($error) and is_array($error)):
			$this->reply_error = @$error[$this->id];
		else:
			$this->reply_error = @$error;
		endif;
					
		if(empty($this->reply_error)):
		
			$this->replied = date::getForDb();
			
			if(empty($this->error)):
				
				if($this->status != 'replied' and $this->status != 'complete'):
				
					if($this->status != 'complete') $this->status = 'replied';					
					
					if($this->user and $this->user->sms_notifications):
						
						$message_data = array(
							'email' => $this->user->contact_email,
							'request' => $this,
						);
						
						$message_tpl = 'offer/email/sms_service_view';
										
						$subject = 'Активации SMS-Услуги: ' . @Lib::config('sms.service', $this->service, 'details');
															
						Lib::sendEmail($subject, $message_tpl, $message_data);
						
					endif;				
				
				endif;
				
			else:	
				
				$this->status = 'complete';
				$this->completed = date::getForDb();
				
			endif;		
			
		endif;
		
		return $this->save();	
		
	}

	public function completeExpired(){
				
		return $this->db->from($this->table_name)->set('status' , 'complete')->set('reply_error','Could not send reply')->where('status', 'requested')->where('added',date::getForDb('-6 hours'))->update();
		return false;
		
	}
	

}
/* ?> */