<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Контроллер отвечающий за приём SMS
 */

class SMS_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
		$this->auto_render = FALSE;
		
	}
	
	public function index(){
		return Lib::pagenotfound();
	}

/**
 * Функция вывода логов специфичных для SMS
 *
 * @param string $transid - ID транзакции
 * @param string $error - текст ошибки
 * @return void
 */
	private function log($transid, $error){
		Lib::log('SMS ('.$transid.'): '. $error);
	}

/**
 * Функция обработки запросов аггрегатора
 *
 * @param string $aggregator_id - ID аггрегатора
 * @param string $password - пароль присланный аггрегатором
 * @return void
 */	
	public function request($aggregator_id, $password = NULL){
	
		if(!empty($aggregator_id)):
			
			$aggregator = Sms::getAggregator($aggregator_id);
			$aggregatorConf = Sms::getConfig($aggregator_id);
			
			$aggregator_keywords = array();
			foreach($aggregatorConf['service'] as $service)
			{
				$aggregator_keywords[] = $service['keyword'];
			}
						
			if(!empty($aggregator->doesnotexist)){
				Lib::log('SMS Aggregator "'.$aggregator_id.'" does not exist');
				header('HTTP/1.1 403 Forbidden');
				exit;
			};
			
			if(!empty($aggregatorConf['IP']) and $aggregatorConf['IP'] != Input::instance()->ip_address()){
				Lib::log('SMS Aggregator "'.$aggregator_id.'" invalid IP:' . Input::instance()->ip_address());				
				header('HTTP/1.1 403 Forbidden');
				exit;
			};

			if(!empty($aggregatorConf['password']) and $aggregatorConf['password'] != @$password){
				Lib::log('SMS Aggregator "'.$aggregator_id.'" incorrect password "'.$password.'"');				
				header('HTTP/1.1 403 Forbidden');
				exit;
			};
			
			$aggregatorRequests = $aggregator->getRequest();
			
			$toSend = array();
			
			foreach($aggregatorRequests as $item):

				$item['keyword'] = utf8::strtolower((string) $item['keyword']);
				
				if(!empty($item['message'])):
					
					if(preg_match('/^\d+$/', $item['message'])):
						$item['offer_id'] = $item['message'];
					elseif(preg_match('/(\d+\D*)?('.addcslashes(join('|',$aggregator_keywords), '+').')\D*(\d+)/i', $item['message'], $matches)):						
						$item['offer_id'] = $matches[3];
					endif;
					
				elseif(preg_match('/('.join('|',$aggregator_keywords).')(\d+)/', $item['keyword'], $matches)):
					$item['keyword'] = $matches[1];
					$item['offer_id'] = $matches[2];									
				endif;		
							
				$request_is_new = false;
				if(!$request = ORM::factory('sms_request', array('transaction_id' => $item['transaction_id'])) or $request->id == 0):
					$request = new SMS_Request_Model;
					$request_is_new = true;
				endif;
				
				$request->aggregator = $aggregator_id;				
				$request->offer_id = (int) $item['offer_id'];
				
				if(!in_array($item['keyword'] , $aggregator_keywords)):
				
					$error = Sms::prepareMessage(Lib::config('sms.text','invalid_keyword'), array('keyword'=>$item['keyword']), $aggregatorConf);
					$this->log($item['transaction_id'], $error);
					$request->service = '';
					
				else:
				
					$request->service = Sms::getServiceByKeyword($aggregator_id, $item['keyword']);
					
				endif;

				if(empty($error)):
									
					if($request->offer_id != 0 and $offer = ORM::factory('offer')->where('status', 'enabled')->find($request->offer_id) and $offer->id != 0):
						
						$request->user_id = $offer->user_id;

						$data['offer'] = $offer->id;
						
						switch($request->service):
							case 'mark':
								$offer->setMarked(Lib::config('sms.service',$request->service,'amount'));
								$data['till'] = date::getSimple($offer->marked_till);
								$request->reply = Sms::prepareMessage(Lib::config('sms.service',$request->service,'confirmation'), $data, $aggregatorConf);
							break;
							case 'position':
								$offer->setPosition();
								$request->reply = Sms::prepareMessage(Lib::config('sms.service',$request->service,'confirmation'), $data, $aggregatorConf);
							break;
							case 'premium':
								$offer->setPremium(Lib::config('sms.service',$request->service,'amount'));
								$data['till'] = date::getSimple($offer->premium_till);
								$request->reply = Sms::prepareMessage(Lib::config('sms.service',$request->service,'confirmation'), $data, $aggregatorConf);
							break;
						endswitch;
						
					else:
					
						$error = Sms::prepareMessage(Lib::config('sms.text','error_no_offer'), array('offer'=>$item['offer_id']), $aggregatorConf);
						$this->log($item['transaction_id'], $error);
						
					endif;					
				
				endif;
			
				
				if(!empty($error)):
			
					$request->reply = $request->error = $error;
					
				endif;
				
				if(!empty($request_is_new)):
					$request->setValuesFromArray($item);	
					
					if($aggregatorConf['notifications_status'])
					{
						$request->status = 'requested';						
					}
					else
					{
						$request->status = 'complete';
					}
					
					$request->save();
				endif;
				
				$message = $request->as_array();			
				$message['text'] = $request->reply;
				
				$toSend['data'][] = $message;
				$toSend['request'][] = $request;
							
			endforeach;

/**
 * Отправляем ответ
 */								
							
			if($send_error = $aggregator->sendMessage($toSend['data'], $aggregatorConf['response_is_sync'])):
				
				if(!is_array($send_error)) $serror = array($send_error);
				else $serror = $send_error;
				
				foreach($serror as $err):
					Lib::log('SMS-notifications to "'.$aggregator_id.'" error: ' . $err);
				endforeach;
			endif;
						
			foreach($toSend['request'] as $i => $item):
				
				$item->sendReply($send_error?$send_error:'');
				
			endforeach;	
		
		else:
		
			header('HTTP/1.1 403 Forbidden');
			exit;
			
		endif;
			
	}

/**
 * Функция отказа от рассылки SMS
 *
 * @param int $id - ID запроса
 * @return void
 */			
	public function complete($id){
	
		if(!$this->hasAccess('enabled')) return;
		
		if(empty($id)):
			$this->errors->add('ID is missing.');
			return;
		endif;
		
		$this->auto_render = true;
		
		$request = new SMS_Request_Model($id);
		
		if($request->id == 0) { return; }
		
		if((empty($request->user_id) or $request->user_id != $this->user->id)  and !$this->hasAccess('moderator')) return;
		
		$request->status = 'complete';
		$request->completed = date::getForDb();
		
		$request->save();
		
		$this->template->browser_redirect = array('/offer/'.$id.'/',15);
		$this->messages->add('SMS - уведомления для объявления №'. $id .' отключены.');
		
	}
	
}