<?php defined('SYSPATH') or die('No direct script access.');

class Cron_Controller extends Controller_Core {

	public function __construct()
	{
		parent::__construct();
		
		if(!empty($_GET['output'])) $this->outputEnabled = true;
		else $this->outputEnabled = false;
		
		if(PHP_SAPI != 'cli' and (!$this->user = Auth::instance()->authorize() or !$this->user->is_administrator))
			Lib::pagenotfound();
		
		if(PHP_SAPI == 'cli')
		{
			ini_set('max_execution_time', 120);
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
		}

	}

	public function index()
	{


		try{
			$this->output('Proverka krona: cron rabotaet',1);
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	

		
	}
	
	public function output($str, $noLineBreak = FALSE){
		if(PHP_SAPI != 'cli' or $this->outputEnabled):
			echo $str;
			if(empty($noLineBreak)){
				if(PHP_SAPI != 'cli') echo '<br>';
				echo "\n";
			}
		endif;
	}

	public function deleteFakeUsers()
	{
	
		try{
		
			$this->output('UDALENIE NEPODTVERZHDENNIH POLZOVATELEY:');
	
			$userlist = ORM::factory('user')
				->where(array(
					'status'=>'disabled',
					'registered <' => date::getForDb( 
						strtotime('-' . Lib::config('app.not_activated_user_expiration_days') .' days')
					)
				))->find_all();
				
			$this->output('Naydeno: ' . $userlist->count() .' polzovateley');
			
			foreach($userlist as $user):
				$this->output('Udalyaetsa polzovatel: '.$user->own_name.' ('.$user->id.')');
				$user->delete();
			endforeach;
		
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}
	
	public function disableExpiredOffers()
	{
		try{
		
			$this->output('OTKLYUCHENIE ISTEKSHIH OBYAVLENIY:');
			
			$affectedlist = ORM::factory('offer')->setExpiredOffers();
		
			$this->output('Otklucheno: ' . count($affectedlist) .' ob`yavleniy');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
		try{
		
			$this->output('UDALENIE STARIH OBYAVLENIY:');
			
			$affectedlist = ORM::factory('offer')->removeOldOffers(PHP_SAPI == 'cli' ? 118:25);
		
			$this->output('Udaleno: ' . $affectedlist .' ob`yavleniy');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}
	
	public function createShortDescriptionsForOffers()
	{
		try{
		
			$this->output('createShortDescriptionsForOffers:');
			
			$affectedlist = ORM::factory('offer')->find_all();
		
			foreach($affectedlist as $offer):
				$offer->short_description = text::limit_chars(text::untypography(strip_tags($offer->description)), 100, '...', TRUE);

				$offer->save();
			endforeach;
		
			$this->output('ispravleno: ' . count($affectedlist) .' ob`yavleniy');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}

	public function deleteOldMessages()
	{
		try{
		
			$this->output('UDALENIE STARIH SOOBSCHENIY:');
			
			$limit = date::getForDb(strtotime('-'.Lib::config('app.message_alive_days').' days'));
			
			if($affectedlist = ORM::factory('message')->where('added < ', $limit)->count_all()):
			
				ORM::factory('message')->where('added < ', $limit)->delete_all();
					
				
			endif;
			
			$this->output('Udaleno: ' . $affectedlist .' soobscheniy');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}

	public function notifyExpiring()
	{
	
		try{
			$this->output('NAPOMINANIYA OB ISTEKAYUSCHIH OBYAVLENIYAH:');
			
			ORM::factory('offer_notification')
				->where('added < ', date::getForDb(strtotime('-' . (Lib::config('app.offer_expiration_notification_days') + 1).' days')))
				->in('type', array('expiration','1_day_expiration','expired'))
				->delete_all();
			
			$expiringOffersList = ORM::factory('offer')
				->where(array(
					'has_not_user' => 0,
					'expiration >' => date::getForDb(strtotime('-1 day')), 
					'expiration <='=> date::getForDb(strtotime('+' . Lib::config('app.offer_expiration_notification_days').' days'))))
				->in('status', array('enabled', 'expired'))
				->find_all();	

			$notifications_count = 0;
			
			if($expiringOffersList->count()):
			
				$users = ORM::factory('user')->in('id', $expiringOffersList->getValues('user_id'))->find_all()->as_id_array();
			
				$notifications = ORM::factory('offer_notification')
					->in('offer_id', $expiringOffersList->getIDs())
					->in('type', array('expiration','1_day_expiration','expired'))
					->find_all()->as_id_array('offer_id');
			
			
				foreach($expiringOffersList as $offer):
					
					if(empty($users[$offer->user_id]) || $users[$offer->user_id]->notifications == 'disabled') continue;
						
					if($offer->status == 'expired'):
					
						if(!empty($notifications[$offer->id])):
							if($notifications[$offer->id]->type == 'expired') continue;
							$offer_notification = $notifications[$offer->id];
						else:
							$offer_notification = ORM::factory('offer_notification');
						endif;
						$subject = 'Завершился показ Вашего объявления!';
						$offer_notification->type = 'expired';
						$is_expired = true;
						
					elseif($offer->expiration <= date::getForDb(strtotime('+1 day'))):
					
						if(!empty($notifications[$offer->id])):
							if($notifications[$offer->id]->type == '1_day_expiration') continue;
							
							$offer_notification = $notifications[$offer->id];
						else:
							$offer_notification = ORM::factory('offer_notification');
						endif;
						$subject = 'Завершается показ Вашего объявления!';
						$offer_notification->type = '1_day_expiration';
						$is_expired = false;
					else:
						if(!empty($notifications[$offer->id])):
							if($notifications[$offer->id]->type == 'expiration') continue;
							$offer_notification = $notifications[$offer->id];
						else:
							$offer_notification = ORM::factory('offer_notification');
						endif;
						$subject = 'Завершается показ Вашего объявления!';
						$offer_notification->type = 'expiration';
						$is_expired = false;
					endif;
					
					if($offer->notification_email != ''):
	
						$message_data = array(
							'email' => $offer->notification_email,
							'offer_title' => text::untypography($offer->fulltitle),
							'expiration' => $offer->expiration,
							'offer_url' => $offer->url,
							'offer_url_expiration' => $offer->url_expiration,
							'offer_url_delete' => $offer->url_delete,
							'offer_url_premium' => $offer->url_premium,
							'offer_url_mark' => $offer->url_mark,
							'is_expired' => $is_expired
						);
				
						$message_tpl = 'offer/email/notifications_expiring_view';
				
						Lib::sendEmail($subject, $message_tpl, $message_data);				
					endif;
					
					$offer_notification->{$offer->foreign_key()} = $offer->id;
					$offer_notification->save();
	
					$notifications_count++;
						

				endforeach;
			
			endif;
			
			$this->output('Otpravleno: ' . $notifications_count .' napominaniy');
		
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}

	public function notifyExpiringMarked()
	{
	
		try{
			$this->output('NAPOMINANIYA OB ISTEKAYUSCHIH MARKED I PREMIUM OBYAVLENIYAH:');
			
			ORM::factory('offer_notification')->where(
				array(
					'added < ' => date::getForDb(strtotime('-' . Lib::config('app.offer_marked_expiring_notification_days').' days')),
					'type' => 'marked'
					))
				->delete_all();
			
			$expiringOffersList = ORM::factory('offer')
				->where(array(
					'has_not_user' => 0,
					'(('.
					'premium_till > "'. date::getForDb() .'" and '.
					'premium_till <= "'. date::getForDb(strtotime('+' . Lib::config('app.offer_premium_expiring_notification_days').' days')).'"'.
					') or ('.
					
					'marked_till > "'. date::getForDb() .'" and '.
					'marked_till <= "'. date::getForDb(strtotime('+' . Lib::config('app.offer_marked_expiring_notification_days').' days')).'"'.
					
					'))', 
					'status'=>'enabled' ))
				->find_all();		
			
			
			if($expiringOffersList->count()):
				
				$users = ORM::factory('user')->in('id', $expiringOffersList->getValues('user_id'))->find_all()->as_id_array();
				
				$notifications = ORM::factory('offer_notification')->in('offer_id', $expiringOffersList->getIDs())->find_all()->as_id_array('offer_id');
				
				$notifications_count = 0;
				
				
				foreach($expiringOffersList as $offer):
		
					if(!empty($users[$offer->user_id]) and empty($notifications[$offer->id]) and $users[$offer->user_id]->notifications != 'disabled'):
		
						if($offer->notification_email != ''):
		
							$message_data = array(
								'email' => $offer->notification_email,
								'offer_title' => text::untypography($offer->fulltitle),
								'expiration' => $offer->is_premium?$offer->premium_till:$offer->marked_till,
								'offer_url' => $offer->url,
								'offer_url_expiration' => $offer->url_expiration,
								'offer_url_premium' => $offer->url_premium,
								'offer_url_mark' => $offer->url_mark,
							);
					
							if($offer->is_premium):
								$message_tpl = 'offer/email/notifications_expiring_premium_view';
								$subject = 'Завершается премиум показ Вашего объявления!';
							else:
								$message_tpl = 'offer/email/notifications_expiring_marked_view';
								$subject = 'Завершается выделенный показ Вашего объявления!';
							endif;
					
							Lib::sendEmail($subject, $message_tpl, $message_data);				
						endif;
		
						$offer_notification = ORM::factory('offer_notification');
						$offer_notification->{$offer->foreign_key()} = $offer->id;
						$offer_notification->type = 'marked';
						$offer_notification->save();
		
						$notifications_count++;
						
					endif;
				endforeach;
				
			endif;
			
			$this->output('Otpravleno: ' . $notifications_count .' napominaniy');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}
	
	
	function updateMediaVersions(){
	
		try{
		
			$this->output('MEDIA Versions Update:');
			$this->output('Checking CSS');
			
			$versionChanged = false;
			
			$configFileData[] = "<?php defined('SYSPATH') or die('No direct script access.');";
			$configFileData[] = '$config[\'css\'] = array(';

			
			foreach(Lib::config('media.css') as $filename => $version):
			
				$filepath = Lib::config('app.WEB_ROOT') . Lib::config('app.CSS_DIR') . '/' . $filename;
				
				if(file_exists($filepath)
					and $modified = filemtime($filepath)
					and $modified != $version
				):
					$version = $modified;
					$versionChanged = true;
				endif;
				
				$configFileData[] = "	'" . $filename . "' => " .$version.",";					

			endforeach;
			
			$configFileData[] = ');';
			
			$this->output('Checking JS');
			
			$configFileData[] = '$config[\'js\'] = array(';
			
			foreach(Lib::config('media.js') as $filename => $version):
			
				$filepath = Lib::config('app.WEB_ROOT') . Lib::config('app.JS_DIR') . '/' . $filename;
				
				if(file_exists($filepath)
					and $modified = filemtime($filepath)
					and $modified != $version
				):
					$version = $modified;
					$versionChanged = true;
				endif;
				
				$configFileData[] = "	'" . $filename . "' => " .$version.",";					

			endforeach;
			
			$configFileData[] = ');';
			$configFileData[] = '/* ?> */';			
			
			if($versionChanged):
			
				$filename = APPPATH.'config/media.php';
				$content = implode("\n", $configFileData);
				
				// Let's make sure the file exists and is writable first.
				if (is_writable($filename)) {
				
				   if (!file_put_contents($filename, $content)) {
						$this->output("Cannot write to file ($filename)");
						exit;
				   }
				
				} else {
				   echo "The file $filename is not writable";
				   exit;
				}			
				
				Kohana::config_clear('media');
				
				$this->output('Versions updated');
			else:
				$this->output('No update needed');
			endif;
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}
	
	
	public function markExpiredPayments()
	{
		try{
		
			$this->output('OTKLYUCHENIE PROSROCHENNIH PROPLAT:');
			
			$affectedlist = ORM::factory('payment')->markExpired();
		
			$this->output('Otklucheno: ' . count($affectedlist) .' proplat');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
		try{
		
			$this->output('UDALENIE STARIH PROSROCHENNIH PROPLAT:');
			
			$affectedlist = ORM::factory('payment')->deleteExpired();
		
			$this->output('Udaleno: ' . count($affectedlist) .' proplat');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}
	
	
	public function positionOldPremiums()
	{
		try{
		
			$this->output('PODNYATIE PREMIUM:');
			
			$affectedlist = ORM::factory('offer')->positionOldPremiums();
		
			$this->output('Podnyato: ' . count($affectedlist) .' premium obyavleniy');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
		
	}
	
	
	
	public function resendSMSReplies()
	{	
		
		try{
		
			$this->output('OTPRAVKA PROPUSCHENIH SMS OTVETOV:');
		
			ORM::factory('sms_request')->completeExpired();
			
			$requests = ORM::factory('sms_request')->where('status', 'requested')->find_all();
			
			$aggregators = $affectedList = array();
		
			$toSend = array();

			foreach($requests as $item):
			
				if(!isset($aggregators[$item->aggregator])) $aggregators[$item->aggregator] = Sms::getAggregator($item->aggregator);
				
				$toSend[$item->aggregator]['data'][] = $item->as_array() + array('text'=>$item->reply);
				$toSend[$item->aggregator]['request'][$item->id] = $item;
				
			endforeach;
			
			foreach($toSend as $aggregator => $messages):				
				
				$send_response = $aggregators[$aggregator]->sendMessage($messages['data']);
				
				$aggregatorError = '';
				
				foreach($messages['request'] as $id => $item):
					
					if(!is_array($send_response) and !empty($send_response)):
						$send_error = $send_response;
					elseif(is_array($send_response) and !empty($send_response[$id])):
						$send_error = $send_response[$id];
					else:
						$send_error = '';
					endif;
					
					$item->sendReply($send_error?$send_error:'');
					
					if(empty($send_error)):
					
						$affectedList[] = $item->id;
						
					else:
						
						$aggregatorError .= 'request #' .$id. ': ' . $send_error . "\n";
						
					endif;						
					
				endforeach;
				
				if(!empty($aggregatorError)):
						
					Lib::log('SMS-resendReplies to "'.$aggregator."\" error: \n" . $aggregatorError);
						
				endif;
			
			endforeach;			
		
			$this->output('Otpravleno: ' . count($affectedList) .' iz ' . $requests->count() . ' otvetov na SMS');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
	}
	
	
	public function sendSMSNotifications()
	{	
		
		try{
		
			$this->output('OTPRAVKA SMS UVEDOMLENIY:');
		
			ORM::factory('sms_request')->completeExpired();
			
			$requests = ORM::factory('sms_request')->where('status', 'replied')->orderby('notice_sent', 'desc')->find_all();
		
			$aggregators = $affectedList = array();
		
			$offersSent = $phonesSent = array();
		
			$offerIDs = $requests->getValues('offer_id');
			
			if(count($offerIDs) 
				and $offers = ORM::factory('offer')->in('id', $offerIDs)->find_all() 
				and $offers->count()
				and $userIDs = $offers->getValues('user_id')):
								
				$users = ORM::factory('user')->in('id', $userIDs)->find_all()->as_id_array();
				$messages = ORM::factory('message')->countForOffer($offerIDs, 'new');
				$offers = $offers->as_id_array();
				
				$toSend = array();				
				
				foreach($requests as $item):					
					
					if(!isset($aggregators[$item->aggregator])):
						$aggregators[$item->aggregator] = Sms::getAggregator($item->aggregator);
						$aggregatorConf[$item->aggregator] = Lib::config('sms.aggregators', $item->aggregator);
					endif;
					
					if((time() - strtotime($item->notice_sent)) < date::$periods['hours'] * $aggregatorConf[$item->aggregator]['notification_interval']):
						$offersSent[$item->offer_id] = true;
					endif;
					
					if(empty($offersSent[$item->offer_id])):
					
						$send_error = '';
						
						$offer = $offers[$item->offer_id];						

						if(!empty($aggregators[$item->aggregator])):
						
							if(empty($phonesSent[$item->phone]) or $phonesSent[$item->phone] < $aggregatorConf[$item->aggregator]['max_sms_per_user_in_one_hour']):
							
								$toSend[$item->aggregator]['request'][$item->id] = $item;
								$offersSent[$offer->id] = true;
								
								if($users[$offer->user_id]->sms_notifications == 'enabled'):

									$message = $item->as_array();
		
									$data['offer'] = $offer->id;
									
									$data['title'] = $offer->title;
									
									$data['messages'] = !empty($messages[$offer->id])?$messages[$offer->id]:0;
									$data['views'] = $offer->views_count;							
								
									$message = $item->as_array();
									$message['text'] = Sms::prepareMessage(Lib::config('sms.text', 'offer_notification'), $data, $aggregatorConf[$item->aggregator]);
									
									$toSend[$item->aggregator]['data'][] = $message;
									
									@$phonesSent[$item->phone]++;									
									
								endif;		
							endif;						
						endif;						
						
					endif;
															
				endforeach;
				
				foreach($toSend as $aggregator => $messages):
				
					$send_response = $aggregators[$aggregator]->sendMessage($messages['data']);
					
					$aggregatorError = '';	
														
					foreach($messages['request'] as $id => $item):
				
						if(!is_array($send_response) and !empty($send_response)):
							$send_error = $send_response;
						elseif(is_array($send_response) and !empty($send_response[$id])):
							$send_error = $send_response[$id];
						else:
							$send_error = '';
						endif;
				
						if(empty($send_error)):
				
							$item->notice_sent = date::getForDb();
							$item->notice_count++;
							
							if($item->notice_count >= $aggregatorConf[$item->aggregator]['notifications_total']):
								$item->status = 'complete';
								$item->completed = date::getForDb();
							endif;
				
							$item->save();
					
							$affectedList[] = $item->id;
							
						else:

							$aggregatorError .= 'request #' .$id. ': ' . $send_error . "\n";
							
						endif;
						
					endforeach;
						
					if(!empty($aggregatorError)):
						
						Lib::log('SMS-notifications to "'.$aggregator."\" error: \n" . $aggregatorError);
						
					endif;
				
				
				endforeach;
				
			endif;
			
			$this->output('Otpravleno: ' . count($affectedList) .' iz ' . $requests->count() . ' otvetov na SMS');
			
		} catch (Kohana_Exception $e) {
			@Lib::log($e);
			print_r($e);
		}	
	}
	
}
/* ?> */