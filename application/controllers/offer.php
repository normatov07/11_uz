<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Offers controller
 */
class Offer_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
		$this->modelName = 'offer';
//		$this->parent_title = 'Объявление';
	}

	private function isOwner(){
		if($this->isLoggedIn() and !empty($this->offer) and ($this->offer->is_viewed_by_owner or $this->isModerator())) return true;
		return false;
	}

	private $EDITMODE = false;

/**
 * Просмотр объявления
 */
	public function index($offer_id = NULL)
	{
		if($offer_id == NULL){ Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		$this->view = new View('offer/offer_view');

		$this->view->archived = false;

		if( $this->offer->is_deleted):

			if(!$this->isModerator()):
				$this->view->archived = TRUE;
			endif;

		endif;

		if($this->offer->is_disabled):

			if(!$this->isOwner()):
				$this->view->archived = TRUE;
			endif;

		endif;

		if($this->offer->is_expired):

			if(!$this->isOwner()):
				$this->view->archived = TRUE;
			endif;

		endif;

		if($this->offer->is_banned or $this->offer->is_disabled or $this->offer->is_expired):

			if(!$this->isOwner()):
				$this->view->hideContent = TRUE;
			endif;

		endif;

		if($this->offer->is_user_banned):

			if(!$this->isModerator() and !$this->offer->is_viewed_by_owner):
				$this->view->hideContent = TRUE;
			endif;

		endif;

		$this->title = $this->offer->title . ' - ' . $this->offer->category->title . ' - ' . $this->offer->region->title;
		if($this->offer->category->has_district and $this->offer->region->has_district and $this->offer->district_id > 0) $this->title .= ' (' . $this->offer->district->title . ')';

		$this->page_description = text::limit_chars(text::untypography(strip_tags($this->offer->description)), 200, '...', TRUE);
		$this->page_description .= ' ' . $this->offer->region->title;








		if($this->offer->district_id > 0) $this->page_description .= ' ('.$this->offer->district->title.')';
		$this->template->titleInView = true;

		// кодовое имя родительской директории
		$this->template->category_parent_codename =  $this->offer->category->parents[0]->codename;
		$this->view->category_parent_codename =  $this->offer->category->parents[0]->codename;

		$this->addJs('jquery.jqModal.js');
		$this->addJs('offer.js?20131101');
		$this->addJs('breadcrumbs.js');

		if(!$this->offer->is_viewed_by_owner and !$this->isModerator()) $this->offer->addViewsCount();

		$this->view->obj = $this->offer;


		//$this->fb_title = $this->offer->title;
		//$this->fb_image = 'http://zor.uz'.$this->offer->pictures[0]->f('big');







		if(!empty($this->view->archived) or !empty($this->view->hideContent)):
			$this->noindex = true;
		endif;

/**
 * Переход после изменения
 */
		if(Session::instance()->get('offer_added') != NULL):
			$this->messages->add('Объявление успешно добавлено!');
			Session::instance()->delete('offer_added');
			$checkerrors = true;
		elseif(Session::instance()->get('offer_edited') != NULL):
			$this->messages->add('Объявление отредактировано!');
			Session::instance()->delete('offer_edited');
			$checkerrors = true;
		endif;

		if(!empty($checkerrors) and Session::instance()->get('add_error') != NULL):
			$this->errors->add(Session::instance()->get('add_error'));
			Session::instance()->delete('add_error');
		endif;

		if(!empty($checkerrors) and Session::instance()->get('warning') != NULL):
			$this->warnings->add(Session::instance()->get('warning'));
			Session::instance()->delete('warning');
		endif;


/**
 * Вытаскиваем другие объявления автора
 */
		if($this->offer->has_user and $this->offer->user->link_to_other_offers == 'enabled'):
			$this->view->otherOwnersOffers = $this->offer->find_all_owners_offers(/*$amount = */6, /*$withImage = */TRUE);
			if($this->view->otherOwnersOffers->count()):
				$offerIDs = array_keys($this->view->otherOwnersOffers->select_list());
				if(count($offerIDs)) $this->view->pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);
			endif;
		endif;



/**
 * Рекламные объявления
 */

		$this->view->rolist = null; //ORM::factory('ro')->limit(3)->find_in_category($this->offer->category->id, TRUE);

		if(!count($this->view->rolist)):

// Вытаскиваем последние объявления из раздела
			$otherOffersList = array();
			$categoryIDs = array_keys($this->offer->category->parents->select_list() + array($this->offer->category->id => $this->offer->category->title));

			$i = count($categoryIDs) - 1;

			while(isset($categoryIDs[$i])):

				$otherOffersList = ORM::factory('offer')->where('id !=', $this->offer->id)->where('type_id =', $this->offer->type_id)->orderby('added','desc')->find_all_In_Category($categoryIDs[$i], Lib::config('app.other_offers_offer_view_page'));

				if($otherOffersList->count()):
					break;
				endif;
				$i--;
			endwhile;

			$this->view->otherOffersList = $otherOffersList;

		endif;


	}











/**
 * MESSAGE TO THE OFFER AUTHOR
 */

	public function message($offer_id = NULL){

		if($offer_id == NULL){ Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		$this->addJs('offer.js?20131101');


		$this->title = 'Сообщение автору';
		$this->template->titleInView = true;
		$this->noindex = true;


		if(empty($_POST)) $this->returnViewInAjax = true;



/**
 * Ограничение отправки сообщений
 */
		$message_limit_amount = Lib::config('app.messages_limit_amount');
		$message_limit_period = Lib::config('app.messages_limit_period');


		if($this->isLoggedIn()){
			$message_queue = array_values(ORM::factory('message')->where('user_id',$this->user->id)->orderby('added','desc')->limit($message_limit_amount)->find_all()->getValues('added'));
		}elseif(!$message_queue = Session::instance()->get('message_queue')){
			$message_queue = explode('|', cookie::get('m_q'));
		}

		if(count($message_queue) >= $message_limit_amount && $message_queue[0] > date::getForDb('-'.$message_limit_period.' minutes'))
		{
			$this->view = new View('offer/offer_message_antispam_view');
			$this->view->message_limit_amount = format::declension_numerals($message_limit_amount,'сообщения','сообщений','сообщений');
			$this->view->message_limit_period = date::periodToString($message_limit_period,'minutes');
			$this->view->message_limit_left = date::periodToString(time() - strtotime($message_queue[0]),'seconds');
			$this->view->offer = $this->offer;
			return false;
		}



//*
		try{
//*/
			if (! empty($_POST)):

				if(!empty($_POST['cancel'])):
					$this->redirect = $this->offer->url;

					return;
				else:

					$_POST = new Validation($_POST);

					$_POST->pre_filter('trim',true)
						->pre_filter('strip_tags',true)
						->pre_filter('text::break_long_words',true)

						->add_rules('content', 'required', 'length[2,512]')
						;

					if(!$this->isLoggedIn()):
						$_POST->add_rules('name', 'required', 'length[2,64]')

						->pre_filter('utf8::ucfirst','name')
						->add_rules('email','required',array('valid','email'))
						->add_rules('phone','length[2, 32]', array('valid','phone'))
						->post_filter('format::rawphone','phone')
						->post_filter('strtolower', 'email')

						->add_rules('captcha_code', 'required')
						->add_callbacks('captcha_code', array($this, 'check_captcha'))
						;
					endif;


					if ($_POST->validate()):

						$obj = new Message_Model;

						if($this->isLoggedIn()):
							$obj->{$this->user->foreign_key()} = $this->user->id;
						else:
							$obj->name = @$_POST->name;
							$obj->email = @$_POST->email;
							$obj->phone = @$_POST->phone;
						endif;

						$obj->to_user = $this->offer->user->id;
						$obj->{$this->offer->foreign_key()} = $this->offer->id;
						$obj->content = text::typography($_POST->content);

						if($this->offer->has_user):

							$obj->reply_to_content = '<b>'. $this->offer->fulltitle . "</b>\n" .$this->offer->short_description;

							$obj->save();
						endif;

						if($this->offer->notification_email != ''):

							$message_data = array(
								'email' => $this->offer->notification_email,
								'offer_title' => text::untypography($this->offer->fulltitle),
								'message_content' => text::untypography($_POST->content),
								'author_name' => $obj->sender_name,
								'author_email' => $obj->sender_email,
								'author_phone' => format::phone($obj->sender_phone),
							);

							if($this->offer->has_user):
								$message_data['message_url'] = @$obj->url;
								$message_data['message_url_reply'] = @$obj->url_reply;
							endif;

							$message_tpl = 'offer/email/message_view';
							$subject = 'Новое сообщение на Ваше объявление!';

							Lib::sendEmail($subject, $message_tpl, $message_data);

						endif;

						$this->messages->add('Ваше сообщение отправлено!');

						$this->redirect = $this->offer->url_message_success;

/**
 * Выставляем лимит сообщений
 */
						array_push($message_queue, $obj->added);
						if(count($message_queue) > $message_limit_amount) array_shift($message_queue);

						Session::instance()->set('message_queue', $message_queue);
						cookie::set('m_q', join('|',$message_queue), $message_limit_period*60);

						return;

					else: // is valid
						$this->errors->add($_POST->list_errors());
						$this->obj = $_POST;
					endif; //is valid

				endif;

			endif;// POST
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->view = new View('offer/offer_message_view');
		$this->view->offer = $this->offer;
	}

	public function message_success($offer_id = NULL){

		$this->view = new View('offer/offer_message_success_view');
		$this->noindex = true;
		$this->title = 'Ваше сообщение отправлено!';
		$this->view->offer_id = $offer_id;

	}


/**
 * COMPLAINT
 */

	public function complaint($offer_id = NULL){

//		sleep(3);

		if($offer_id == NULL){ Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

//*
		try{
//*/
			if (! empty($_POST)):

				if(!empty($_POST['cancel'])):
					$this->redirect = $this->offer->url;

					return;
				else:

					$_POST = new Validation($_POST);

					$_POST->pre_filter('trim',true)
						->pre_filter('strip_tags',true)
						->pre_filter('text::break_long_words',true)

						->add_rules('title', 'required', 'length[1,128]')
						->add_rules('content', 'required', 'length[3,512]')


						->add_rules('phone','length[2, 32]', array('valid','phone'))
						->post_filter('format::rawphone','phone')
						;

					if(!$this->isLoggedIn()):
						$_POST
						->pre_filter('utf8::ucfirst','name')
						->add_rules('name', 'required', 'length[2,128]')
						->add_rules('captcha_code', 'required')
						->add_callbacks('captcha_code', array($this, 'check_captcha'))
						->add_rules('email','required',array('valid','email'))
						->post_filter('strtolower', 'email')
						;
					endif;


					if ($_POST->validate()):

						$obj = new Complaint_Model;
						$obj->name = !empty($_POST->name)?$_POST->name:$this->user->public_name;
						$obj->email = !empty($_POST->email)?$_POST->email:$this->user->contact_email;

						if($this->isLoggedIn()) $obj->{$this->user->foreign_key()} = $this->user->id;

						$obj->{$this->offer->foreign_key()} = $this->offer->id;

						$titlesarray = Lib::config('app.complaint_titles');

						$obj->title = text::typographyString($titlesarray[$_POST->title]);
						$obj->content = text::typography($_POST->content);

						if($obj->save()):

							$message_data = array(
								'email' => Lib::config('app.email_complaint_address'),
								'offer_title' => text::untypography($this->offer->fulltitle),
								'offer_url' => $this->offer->url,
								'message_title' => text::untypography($obj->title),
								'message_content' => text::untypography($obj->content),
								'author_name' => $obj->name,
								'author_email' => $obj->email,
								'message_url' => @$obj->url,
								'message_url_reply' => @$obj->url_reply,
							);

							$message_tpl = 'offer/email/complaint_view';
							$subject = 'Жалоба на объявление!';

							Lib::sendEmail($subject, $message_tpl, $message_data);

							$this->messages->add('Ваше сообщение отправлено!');

							$this->redirect = $this->offer->url_complaint_success;
							return;

						endif;

					else: // is valid
						$this->errors->add($_POST->list_errors());
						$this->obj = $_POST;
					endif; //is valid

				endif;

			endif;// POST
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->addJs('offer.js?20131101');

		$this->view = new View('offer/offer_complaint_view');
		$this->noindex = true;
		$this->title = 'Жалоба на объявление';
		$this->template->titleInView = true;

		$this->view->offer = $this->offer;

		if(empty($_POST)) $this->returnViewInAjax = true;
	}

	public function complaint_success($offer_id = NULL){

		$this->view = new View('offer/offer_message_success_view');
		$this->noindex = true;
		$this->title = 'Ваша жалоба отправлена!';
		$this->view->offer_id = $offer_id;

	}


	public function remove_complaint($offer_id = NULL, $complaint_id = NULL){

		if(empty($offer_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		if(!$this->hasAccess('moderator')) return;

//*
		try{
//*/

			$complaints = ORM::factory('complaint')->where($this->offer->foreign_key(), $this->offer->id);

			if($complaint_id != NULL):
				$complaints->where('id',$complaint_id);
				$this->data['id'] = $complaint_id;
			else:
				$this->data['all'] = true;
			endif;

			if($complaints->delete_all()):
				$this->title = 'Жалобы удалены!';

			endif;

		//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
	}
/**
 * SEND TO a FRIEND
 */

	public function send($offer_id = NULL){

//		sleep(3);

		if($offer_id == NULL){ Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

//*
		try{
//*/
			if (! empty($_POST)):

				if(!empty($_POST['cancel'])):
					$this->redirect = $this->offer->url;

					return;
				else:

					$_POST = new Validation($_POST);

					$_POST->pre_filter('trim',true)
						->pre_filter('strip_tags',true)
						->pre_filter('text::break_long_words',true)

						->add_rules('content', 'length[2,200]')

						->pre_filter('utf8::ucfirst','name')
						->add_rules('recepient_email','required',array('valid','email'))
						->post_filter('strtolower', 'recepient_email')
						;

					if(!$this->isLoggedIn()):
						$_POST
						->pre_filter('utf8::ucfirst','name')
						->add_rules('name', 'required', 'length[2,128]')
						->add_rules('captcha_code', 'required')
						->add_callbacks('captcha_code', array($this, 'check_captcha'))
						->add_rules('email','required',array('valid','email'))
						->post_filter('strtolower', 'email')
						;
					endif;

					$is_valid = $_POST->validate();

					if ($is_valid):

						$message_data = array(
							'email' => $_POST->recepient_email,
							'offer_title' => text::untypography($this->offer->fulltitle),
							'offer_url' => $this->offer->url,
							'message_content' => $_POST->content,
							'author_name' => !empty($_POST->name)?$_POST->name:$this->user->public_name,
							'author_email' => $obj->email = !empty($_POST->email)?$_POST->email:$this->user->contact_email,
						);

						$message_tpl = 'offer/email/send_view';
						$subject = 'Ваш знакомый рекомендует Вам объявление';

						Lib::sendEmail($subject, $message_tpl, $message_data);

						$this->messages->add('Ваше сообщение отправлено!');

						$this->redirect = $this->offer->url_send_success;
						return;

					else: // is valid
						$this->errors->add($_POST->list_errors());
						$this->obj = $_POST;
					endif; //is valid

				endif;

			endif;// POST
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->addJs('offer.js?20131101');

		$this->view = new View('offer/offer_send_view');
		$this->noindex = true;
		$this->title = 'Отправить объявление другу';
		$this->template->titleInView = true;

		$this->view->offer = $this->offer;

		if(empty($_POST)) $this->returnViewInAjax = true;
	}

	public function send_success($offer_id = NULL){

		$this->view = new View('offer/offer_message_success_view');
		$this->noindex = true;
		$this->title = 'Ссылка на объявление отправлена!';
		$this->view->offer_id = $offer_id;

	}


/**
 * BOOKMARK
 */


	public function bookmark($offer_id = NULL){

		if($offer_id == NULL){ Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		if(!$this->isLoggedIn()){
			$this->redirect = $this->offer->url;
			return;
		}

//*
		try{
//*/

			if(!$this->offer->is_bookmarked):

				$obj = new Bookmark_Model;
				$obj->{$this->user->foreign_key()} = $this->user->id;
				$obj->{$this->offer->foreign_key()} = $this->offer->id;

				$obj->save();

			endif;
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/

		$this->view = new View('offer/offer_bookmark_success_view');
		$this->noindex = true;
		$this->title = 'Закладка на объявление успешно добавлена!';
		$this->view->offer_id = $offer_id;

	}


/**
 * PICTURES
 */


	public function pic($offer_id = NULL, $pic_id = NULL){

//		sleep(2);

		if($offer_id == NULL){ Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		if(!$this->offer->pictures->count()) { $this->redirect = $this->offer->url; return; }

		$this->view = new View('offer/offer_pic_view');
		$this->title = 'Изображение — ' . $this->offer->fulltitle;
		$this->view->offer = $this->offer;
		$this->view->current_pic_id = $pic_id?$pic_id:$this->offer->pictures[0]->id;

		if(empty($_POST)) $this->returnViewInAjax = true;
		$this->addJs('offer.js?20131101');
	}


/**
 * SMS
 */

	public function sms($offer_id = NULL, $service_id = NULL){

		if($offer_id == NULL or $service_id == NULL){ Lib::pagenotfound(); return; }

		$aggregatorConf = Sms::getConfig();

		if(!isset($aggregatorConf['service'][$service_id])) { Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		$this->template->titleInView = true;

		$this->view = new View('offer/offer_'.$service_id.'_view');
		$this->noindex = true;
//		$this->title = 'SMS-Услуги';
		$this->title = Lib::config('sms.service', $service_id, 'title');
		$this->view->offer = $this->offer;
		$this->view->keyword = $aggregatorConf['service'][$service_id]['keyword'];
		$this->view->short_number =$aggregatorConf['service'][$service_id]['short_number'];
		$this->view->price =$aggregatorConf['service'][$service_id]['price'];
		$this->view->currency =$aggregatorConf['service'][$service_id]['currency'];

		$this->view->service_id = $service_id;
		$this->view->service = Lib::config('sms.service', $service_id);
		$this->view->action = Lib::config('sms.service', $service_id, 'details');

		$this->view->providers = $aggregatorConf['providers'];
		$this->view->service_expiration = $aggregatorConf['service_expiration'];

		$this->tariff = new Tariff_Model;
		$tariff_data = $this->tariff->where('service =', $service_id)->where('method =', 'bonus')->find();

		$this->view->tariff = intVal($tariff_data->price);
		if ($this->isModerator())
		{
			$this->view->bonus_amount = $this->offer->user->bonus_amount;
		}
		else
		{
			$this->view->bonus_amount = $this->user->bonus_amount;
		}

		$this->returnViewInAjax = true;

	}


/**
 * EDIT Functions
 */

/**
 * Установка сгорания объявления
 */


	public function expiration($offer_id = NULL){

		if(empty($_POST)) $this->returnViewInAjax = true;

		if(!$this->hasAccess('enabled')) return;

		if(empty($offer_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		if(!$this->offer->is_viewed_by_owner and !$this->hasAccess('moderator')) return;

//*
		try{
//*/
			if (! empty($_POST)):

				if(!empty($_POST['cancel'])):
					$this->redirect = $this->offer->url;
					return;
				elseif($this->isModerator() or $this->offer->expiration < date::getForDb(strtotime('+4 days'))):
					$_POST = new Validation($_POST);

					$_POST->pre_filter('trim',true);

					if ($_POST->validate()):

						if($date = $this->offer->setExpiration(@$_POST->days_to_add)):

							if(!$this->offer->is_viewed_by_owner and $this->offer->notification_email != ''):

								$message_data = array(
									'email' => $this->offer->notification_email,
									'offer_title' => text::untypography($this->offer->fulltitle),
									'offer_url' => $this->offer->url,
									'expiration' => $this->offer->expiration,
								);

								$message_tpl = 'offer/email/expiration_view';
								$subject = 'Показ объявления продлён!';

								Lib::sendEmail($subject, $message_tpl, $message_data);

							endif;

							$this->messages->add('Объявление продлено!');
							$this->data['id'] = $this->offer->id;
							$this->data['date'] = $date;
							$this->data['status'] = $this->offer->status;
							$this->data['status_title'] = Lib::config('app.offer_statuses', $this->offer->status);

							$this->redirect = $this->offer->url_expiration_success;

							return;

						endif;
					else: // is valid
						$this->errors->add($_POST->list_errors());
						$this->obj = $_POST;
					endif; //is valid

				endif;

			endif;// POST
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->addJs('offer.js?20131101');

		$this->view = new View('offer/offer_expiration_view');
		$this->noindex = true;
		$this->title = 'Продление объявления';
		$this->template->titleInView = true;

		$this->view->offer = $this->offer;

	}

	public function expiration_success($offer_id = NULL){

		$this->view = new View('offer/offer_message_success_view');
		$this->noindex = true;
		$this->title = 'Объявление продлено!';
		$this->view->offer_id = $offer_id;

	}


/**
 * Отключение/включение объявления
 */


	public function disable($offer_id = NULL, $remove = FALSE){

		if(!$this->hasAccess('registered') or ($remove and !$this->hasAccess('enabled'))) return;

		if(empty($offer_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { return; }

		if(!$this->offer->is_viewed_by_owner and !$this->hasAccess('moderator')) return;

		if($this->offer->is_banned or $this->offer->is_user_banned or $this->offer->is_deleted) return;

		if($remove):
			/*
            $category_warning = $this->offer->category->check_offers_limit($this->user);

            if ($category_warning['warning_status']):
                $this->errors->add('Физическое лицо может размещать не более 2-х объявлений в выбранном разделе.');
            else:

			endif;
			*/
                if($date = $this->offer->setEnabled()):
                    $this->title = 'Объявление включено!';
                    $this->data['date'] = $date;
                endif;

		else:
			if($date = $this->offer->setDisabled()):
				$this->title = 'Объявление выключено!';
			endif;
		endif;

		$this->noindex = true;

	}

	public function enable($obj_id = NULL){

		return $this->disable($obj_id, TRUE);

	}


/**
 * MODERATION
 */

/**
 * Установить или снять статус Премиум
 */


	public function premium($offer_id = NULL, $remove = FALSE){

		if(empty($_POST) and !$remove) $this->returnViewInAjax = true;

		if(!$this->hasAccess('enabled')) return;

		if(empty($offer_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { return; }

		if(!$this->isModerator() and $this->offer->is_viewed_by_owner):
			$this->redirect = $this->offer->payment_premium;
			return;
		endif;

		if(!$this->hasAccess('moderator')) return;

		if($remove):

			if($this->offer->unsetPremium() and $this->offer->has_user and $this->offer->notification_email != ''):
					$message_data = array(
						'email' => $this->offer->notification_email,
						'offer_title' => text::untypography($this->offer->fulltitle),
						'offer_url' => $this->offer->url,
					);

					$message_tpl = 'offer/email/premium_remove_view';
					$subject = 'С Вашего объявления снят статус Премиум!';

					Lib::sendEmail($subject, $message_tpl, $message_data);
					$this->title = 'С объявления снят статус Премиум';
			endif;

		else:
//*
			try{
//*/
				if (! empty($_POST)):

					if(!empty($_POST['cancel'])):
						$this->redirect = $this->offer->url;
						return;
					else:
						$_POST = new Validation($_POST);

						$_POST->pre_filter('trim',true);

						if ($_POST->validate()):

							if($date = $this->offer->setPremium(@$_POST->count)):

								if($this->offer->has_user and $this->offer->notification_email != ''):
									$message_data = array(
										'email' => $this->offer->notification_email,
										'offer_title' => text::untypography($this->offer->fulltitle),
										'offer_url' => $this->offer->url,
									);

									$message_tpl = 'offer/email/premium_view';
									$subject = 'Ваше объявление стало Премиум объявлением!';

									Lib::sendEmail($subject, $message_tpl, $message_data);
								endif;

								$this->messages->add('Объявление премировано!');
								$this->title = 'Объявление стало премиум';
								$this->data['id'] = $this->offer->id;
								$this->data['date'] = $date;

								$this->redirect = $this->offer->url_premium_success;

								return;

							endif;

						else: // is valid
							$this->errors->add($_POST->list_errors());
							$this->obj = $_POST;
						endif; //is valid

					endif;

				endif;// POST
	//*
			} catch (Kohana_Exception $e) {
						$this->handleException($e);
			}
	//*/
			$this->addJs('offer.js?20131101');

			$this->view = new View('offer/offer_premium_view');
			$this->noindex = true;
			$this->title = 'Премирование объявления';
			$this->template->titleInView = true;

			$this->view->offer = $this->offer;

		endif;

		$this->noindex = true;
	}

	public function premium_success($offer_id = NULL){

		$this->view = new View('offer/offer_message_success_view');
		$this->noindex = true;
		$this->title = 'Объявление премировано!';
		$this->view->offer_id = $offer_id;

	}

	public function unpremium($obj_id = NULL){

		return $this->premium($obj_id, TRUE);

	}
/**
 * Установка и снятие выделения на объявление
 */

	public function mark($offer_id = NULL, $remove = FALSE){

		if(empty($_POST) and !$remove) $this->returnViewInAjax = true;

		if(!$this->hasAccess('enabled')) return;

		if(empty($offer_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { return; }

		if(!$this->isModerator() and $this->offer->is_viewed_by_owner):
			$this->redirect = $this->offer->payment_mark;
			return;
		endif;

		if(!$this->hasAccess('moderator')) return;


		if($remove):
			if($this->offer->unsetMarked() and $this->offer->has_user and $this->offer->notification_email != ''):
				$message_data = array(
					'email' => $this->offer->notification_email,
					'offer_title' => text::untypography($this->offer->fulltitle),
					'offer_url' => $this->offer->url,
				);

				$message_tpl = 'offer/email/marked_remove_view';
				$subject = 'С Вашего объявления снято выделение!';

				Lib::sendEmail($subject, $message_tpl, $message_data);
				$this->title = 'С объявления снято выделение';
			endif;
		else:

//*
			try{
//*/
				if (! empty($_POST)):

					if(!empty($_POST['cancel'])):
						$this->redirect = $this->offer->url;
						return;
					else:
						$_POST = new Validation($_POST);

						$_POST->pre_filter('trim',true);

						if ($_POST->validate()):

							if($date = $this->offer->setMarked(@$_POST->count)):

								if($this->offer->has_user and $this->offer->notification_email != ''):
									$message_data = array(
										'email' => $this->offer->notification_email,
										'offer_title' => text::untypography($this->offer->fulltitle),
										'offer_url' => $this->offer->url,
									);

									$message_tpl = 'offer/email/marked_view';
									$subject = 'Ваше объявление выделено в общем списке!';

									Lib::sendEmail($subject, $message_tpl, $message_data);
								endif;

								$this->messages->add('Объявление выделено!');
								$this->title = 'Объявление выделено';
								$this->data['id'] = $this->offer->id;
								$this->data['date'] = $date;

								$this->redirect = $this->offer->url_premium_success;

								return;

							endif;

						else: // is valid
							$this->errors->add($_POST->list_errors());
							$this->obj = $_POST;
						endif; //is valid

					endif;

				endif;// POST
	//*
			} catch (Kohana_Exception $e) {
						$this->handleException($e);
			}
	//*/
			$this->addJs('offer.js?20131101');

			$this->view = new View('offer/offer_mark_view');
			$this->noindex = true;
			$this->title = 'Выделение объявления';
			$this->template->titleInView = true;

			$this->view->offer = $this->offer;

		endif;

		$this->noindex = true;
	}

	public function mark_success($offer_id = NULL){

		$this->view = new View('offer/offer_message_success_view');
		$this->noindex = true;
		$this->title = 'Объявление выделено!';
		$this->view->offer_id = $offer_id;

	}
	public function unmark($obj_id = NULL){

		return $this->mark($obj_id, TRUE);

	}

/**
 * Поднятие объявления в списке
 */

	public function position($offer_id = NULL, $remove = FALSE){

		if(!$this->hasAccess('enabled')) return;

		if(empty($offer_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { return; }

		if(!$this->isModerator() and $this->offer->is_viewed_by_owner):
			$this->redirect = $this->offer->payment_position;
			return;
		endif;

		if(!$this->hasAccess('moderator')) return;

		if($date = $this->offer->setPosition($offer_id)):
			if($this->offer->has_user and $this->offer->notification_email != ''):
				$message_data = array(
					'email' => $this->offer->notification_email,
					'offer_title' => text::untypography($this->offer->fulltitle),
					'offer_url' => $this->offer->url,
				);

				$message_tpl = 'offer/email/marked_view';
				$subject = 'Ваше объявление поднято в начало списка!';

				Lib::sendEmail($subject, $message_tpl, $message_data);
			endif;

			$this->messages->add('Объявление поднято!');
			$this->title = 'Объявление поднято';
			$this->data['id'] = $this->offer->id;
			$this->data['date'] = $date;

			$this->redirect = $this->offer->url_premium_success;

			return;
		endif;

		$this->noindex = true;
	}

	public function position_success($offer_id = NULL){

		$this->view = new View('offer/offer_message_success_view');
		$this->noindex = true;
		$this->title = 'Объявление поднято!';
		$this->view->offer_id = $offer_id;

	}

/**
 * Блокировка объявления
 */


	public function ban($offer_id = NULL, $remove = FALSE){

		if(empty($_POST) and !$remove) $this->returnViewInAjax = true;

		if(!$this->hasAccess('moderator')) return;

		if($offer_id == NULL) { Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		if(/*!$this->offer->is_viewed_by_owner and*/ !$this->hasAccess('moderator')) return;


		if($remove):
			if($this->offer->unBan()):
				if($this->offer->has_user and $this->offer->notification_email != ''):
					$message_data = array(
						'email' => $this->offer->notification_email,
						'offer_title' => text::untypography($this->offer->fulltitle),
					);

					$message_tpl = 'offer/email/unban_view';
					$subject = 'Ваше объявление разблокировано!';

//					Lib::sendEmail($subject, $message_tpl, $message_data);
				endif;

				$this->messages->add('Объявление разблокировано!');

				$this->data['status'] = $this->offer->status;
				$this->data['status_title'] = Lib::config('app.offer_status', $this->offer->status);
				$this->data['date'] = date::getSimple($this->offer->expiration);

				$this->redirect = $this->offer->url_unban_success;
				return;
			endif;

		else:

	//*
			try{
	//*/
				if (! empty($_POST)):

					if(!empty($_POST['cancel'])):
						$this->redirect = $this->offer->url;
						return;
					else:

						$_POST = new Validation($_POST);

						$_POST->pre_filter('trim',true)
							->pre_filter('strip_tags',true)
							->pre_filter('text::break_long_words',true)

	//						->add_rules('captcha_code', 'required')
	//						->add_callbacks('captcha_code', array($this, 'check_captcha'))
							->add_rules('content', 'length[2,800]')
							;


						if ($_POST->validate()):

								if(@$_POST->predefined_reason == 'other'){
									$reason = @$_POST->content;
								}else{
									$titlesarray = Lib::config('app.ban_reasons');

									$reason = $titlesarray[$_POST->predefined_reason];
								};

								$this->data['prev_status'] = $this->offer->status;

								if($this->offer->setBanned($this->user->id, text::typography($reason))):

									if($this->offer->has_user and $this->offer->notification_email != ''):
										$message_data = array(
											'email' => $this->offer->notification_email,
											'offer_title' => text::untypography($this->offer->fulltitle),
											'offer_description' => text::untypography($this->offer->short_description),
											'message_content' => @$reason,
										);

										$message_tpl = 'offer/email/ban_view';
										$subject = 'Ваше объявление заблокировано!';

										Lib::sendEmail($subject, $message_tpl, $message_data);
									endif;

									$this->messages->add('Объявление заблокировано!');

									$this->data['id'] = $this->offer->id;

									$this->redirect = $this->offer->url_ban_success;
									return;
								endif;

						else: // is valid
							$this->errors->add($_POST->list_errors());
							$this->obj = $_POST;
						endif; //is valid

					endif;

				endif;// POST
	//*
			} catch (Kohana_Exception $e) {
					$this->handleException($e);
			}
	//*/

		endif;
		$this->addJs('offer.js?20131101');

		$this->view = new View('offer/offer_ban_view');
		$this->view->no_reason = false;//(!$this->isModerator() or $remove);
		$this->title = 'Блокировка объявления';
		$this->template->titleInView = true;
		$this->noindex = true;

		$this->view->offer = $this->offer;

	}


	public function unban($obj_id = NULL){

		return $this->ban($obj_id, TRUE);

	}

	public function ban_success($offer_id = NULL){

//		$this->view = new View('offer/offer_delete_success_view');
		$this->title = 'Объявление заблокировано!';
		$this->noindex = true;
//		$this->view->offer_id = $offer_id;

	}
	public function unban_success($offer_id = NULL){

//		$this->view = new View('offer/offer_delete_success_view');
		$this->title = 'Объявление разблокировано!';
		$this->noindex = true;
//		$this->view->offer_id = $offer_id;

	}



/**
 * Удаление пользователем
 */


	public function delete($offer_id = NULL){

		if(empty($_POST)) $this->returnViewInAjax = true;
		elseif(!empty($_POST['remove_totally'])) return $this->remove($offer_id);

		if(!$this->hasAccess('registered')) return;

		if($offer_id == NULL) { Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		if(!$this->offer->is_viewed_by_owner and !$this->hasAccess('moderator')) return;

//*
		try{
//*/
			if (! empty($_POST)):

				if(!empty($_POST['cancel'])):
					$this->redirect = $this->offer->url;
					return;
				else:

					$_POST = new Validation($_POST);

					$_POST->pre_filter('trim',true);

					if(!$this->offer->is_viewed_by_owner):
						$_POST
						->pre_filter('strip_tags',true)
						->pre_filter('text::break_long_words',true)

//						->add_rules('captcha_code', 'required')
//						->add_callbacks('captcha_code', array($this, 'check_captcha'))
						->add_rules('content', 'length[2,200]')
						;
					endif;

					if ($_POST->validate()):

						if(@$_POST->predefined_reason != '' and !$this->offer->is_viewed_by_owner and $this->offer->has_user and $this->offer->notification_email != ''):

							if(@$_POST->predefined_reason == 'other'):
								$reason = @$_POST->content;
							else:
								$reason = Lib::config('app.remove_reasons', $_POST->predefined_reason);
							endif;

							$message_data = array(
								'email' => $this->offer->notification_email,
								'offer_title' => text::untypography($this->offer->fulltitle),
								'message_content' => @$reason,
							);

							$message_tpl = 'offer/email/delete_view';
							$subject = 'Ваше объявление удалено!';

							Lib::sendEmail($subject, $message_tpl, $message_data);
						endif;



						$this->data['id'] = $this->offer->id;


						if(!empty($_POST->remove_totally)):
							if($this->offer->delete()):


								$this->messages->add('Объявление полностью удалено!');
								$this->redirect = $this->offer->url_delete_success;
								return;

							endif;
						else:
							if($this->offer->setDeleted(NULL, $this->user->id)):


								$this->messages->add('Объявление удалено!');
								$this->redirect = $this->offer->url_delete_success;

								return;
							endif;
						endif;


					else: // is valid
						$this->errors->add($_POST->list_errors());
						$this->obj = $_POST;
					endif; //is valid

				endif;

			endif;// POST
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->addJs('offer.js?20131101');

		$this->view = new View('offer/offer_delete_view');
		$this->view->delete_mode = true;
		$this->view->no_reason = $this->offer->is_viewed_by_owner;
		$this->title = 'Удаление объявления';
		$this->template->titleInView = true;
		$this->noindex = true;

		$this->view->offer = $this->offer;

		if(empty($_POST)) $this->returnViewInAjax = true;

	} // delete

/* восстановление */
	public function undelete($offer_id = NULL){

		if(!$this->hasAccess('enabled')) return;

		if($offer_id == NULL) { Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		if(!$this->offer->is_viewed_by_owner and !$this->hasAccess('moderator')) return;

//*
		try{
//*/

			$this->data['id'] = $this->offer->id;

			if($this->offer->unDelete()):
				$this->title = 'Объявление восстановлено!';
//				$this->messages->add($this->title);
				return;
			endif;
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/

		$this->noindex = true;

	} // undelete

/**
 * Полное Удаление объявления
 */


	public function remove($offer_id = NULL){

		if(empty($_POST)) $this->returnViewInAjax = true;

		if(!$this->hasAccess('registered')) return;

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; }

		if( !$this->offer->is_viewed_by_owner and !$this->hasAccess('moderator')) return;

//*
		try{
//*/
			if (! empty($_POST)):

				if(!empty($_POST['cancel'])):
					$this->redirect = $this->offer->url;
					return;
				else:

					$_POST = new Validation($_POST);

					$_POST->pre_filter('trim',true)
						->pre_filter('strip_tags',true)
						->pre_filter('text::break_long_words',true)

//						->add_rules('captcha_code', 'required')
//						->add_callbacks('captcha_code', array($this, 'check_captcha'))
						->add_rules('content', 'length[2,200]')
						;


					if ($_POST->validate()):

						if(@$_POST->predefined_reason != '' and !$this->offer->is_viewed_by_owner and $this->offer->has_user and $this->offer->notification_email != ''):

							if(@$_POST->predefined_reason == 'other'):
								$reason = @$_POST->content;
							else:
								$reason = Lib::config('app.remove_reasons', $_POST->predefined_reason);
							endif;

							$message_data = array(
								'email' => $this->offer->notification_email,
								'offer_title' => text::untypography($this->offer->fulltitle),
								'message_content' => @$reason,
							);

							$message_tpl = 'offer/email/delete_view';
							$subject = 'Ваше объявление удалено!';

							Lib::sendEmail($subject, $message_tpl, $message_data);
						endif;

						$this->data['id'] = $this->offer->id;

						if($this->offer->delete()):

							$this->messages->add('Объявление полностью удалено!');

							$this->redirect = $this->offer->url_delete_success;
							return;

						endif;

					else: // is valid
						$this->errors->add($_POST->list_errors());
						$this->obj = $_POST;
					endif; //is valid

				endif;

			endif;// POST
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->addJs('offer.js?20131101');

		$this->view = new View('offer/offer_delete_view');
		$this->view->no_reason = !$this->isModerator();
		$this->title = 'Полное удаление объявления';
		$this->template->titleInView = true;
		$this->noindex = true;

		$this->view->offer = $this->offer;

		if(empty($_POST)) $this->returnViewInAjax = true;

	}

	public function delete_success($offer_id = NULL){

//		$this->view = new View('offer/offer_delete_success_view');
		$this->title = 'Объявление удалено!';
		$this->noindex = true;
//		$this->view->offer_id = $offer_id;

	}





/**
 * Добавление и редактирование объявления
 */

	public function unique_email(Validation $v)
	{
		if(empty($v->user_email)) return true;

		$user_id = ORM::factory('user')->select('id')->find(array('email' => $v->user_email))->id;

		if($user_id == 0 or (!empty($v->id) and $v->id == $user_id)) return true;

		$v->add_error('user_email', 'email_exists');

		return false;
	}

	public function is_agreed(Validation $v)
	{
		if(!empty($v->user_accept_disclaimer)) return true;
		$v->add_error('user_accept_disclaimer', 'agreed_with_disclaimer');

		return false;
	}


	public function contact_required(Validation $v)
	{
		if(empty($v->phone) and empty($v->email)):
			$v->add_error('phone', 'Введите "email" или номер телефона');
			return false;
		endif;

		return true;
	}

	public function check_password(Validation $v)
	{

		if($v->user_password != $v->user_repeat_password):
			$v->add_error('user_password', 'password_confirm');
			return false;
		else:
			return true;
		endif;

	}


	public function check_price(Validation $v)
	{
		if(!empty($v->price)) $v->price -= 0;
		if(!empty($v->price_to)) $v->price_to -= 0;

		if($v->price_type == 'fixed'){
			$v->price_to = '';
			return true;
		};

		if(!empty($v->price_to) and @$v->price_to < @$v->price):
			$v->add_error('price', 'Цена "до" должна быть больше цены "от".');
		endif;

		return true;
	}


	private function _makeAutoTitle($format, $values){

		krsort($values);

		foreach($values as $var => $value){
			$pattern[] = '/\$'.$var.'([^a-z_]|$)/i';
			$replacement[] = $value.'\\1';
		}

		$formatted = preg_replace($pattern, $replacement, $format);

		while(preg_match('/\{([^\{\}]*\$[a-z_]+(.*?))\}/i',$formatted, $matches)){
			if(!empty($matches[2]) and $subs_count = substr_count($matches[2],'{')){
				$mask = '/\{[^\{\}]*\$[a-z_]+.*?'.str_repeat('\}.*?',$subs_count).'\}/i';
				$formatted = preg_replace($mask,'',$formatted);
			}else{
				$formatted = str_replace($matches[0], '', $formatted);
			}
		}

		$formatted = preg_replace('/[\{\}]/','',$formatted);

		$formatted = preg_replace('/\$[a-z_]+([^a-z_]|$)/i', '\\1', $formatted);

		return $formatted;

	}

	public function add($category_id = NULL)
	{

        if (!$this->isLoggedIn()) {
            $this->view = new View('auth/login_view');
            return;
        }

		$this->title = 'Разместить объявление бесплатно';

		if($this->isLoggedIn() and !$this->hasAccess('notbanned')) return;

		$this->view = new View('offer/offer_edit_view');

		$this->template->titleInView = true;

		$this->addJs('offer_edit.js?20131101');

		if(empty($category_id)):
			if(!empty($_GET['id'])):
				$category_id = $_GET['id'];
			elseif(!empty($_POST)):
				if(!empty($_POST['category_id'])):
					$category_id = $_POST['category_id'];
				elseif(!empty($_POST['get_category'])):
					$this->errors->add('Выберите раздел из списка');
				endif;
				if(!empty($_POST['region_id'])):
					$this->view->region_id = $_POST['region_id'];
				elseif(!empty($_POST['get_region'])):
					$this->errors->add('Выберите регион из списка');
				endif;
			endif;
		endif;

		if(!empty($category_id)):
			if(preg_match('/^\d+$/', $category_id)):
				$category = ORM::factory('category', $category_id);
			else:
				$category = ORM::factory('category')->find(array('codename' => $category_id));
			endif;
		endif;

		if(!empty($category) and $category->id != 0) {
            $this->view->category = $category;
            //$this->view->category_warning = $category->check_offers_limit($this->user);
        }

		if(!empty($_POST) and empty($_POST['get_category']) and empty($_POST['get_region'])):
			/*
            // проверка лимита объявлений для раздела при добавлении объявления
            if (isset($this->view->category_warning) && $this->view->category_warning['warning_status']):
                $this->errors->add('Физическое лицо может размещать не более 2-х объявлений в выбранном разделе.');
                return;
            endif;
			*/

			if(!$this->save()):
				$this->view->obj = $_POST;
			endif;

		endif;

	}

	public function add_success($offer_id = NULL){
		$this->view = new View('offer/offer_add_success_view');
		$this->view->offer_id = $offer_id;

		if(!$this->isLoggedIn() or $this->isNotActivated()):
			$this->template->dontDisplayNotActivatedWarning = true;
			$this->title = 'Ваши данные сохранены и ожидают активации';
		else:
			$this->title = 'Ваше объявление добавлено!';
			$this->template->browser_redirect = array('/offer/'.$offer_id.'/',15);
		endif;

		if(Session::instance()->get('add_error') != NULL):
			$this->errors->add(Session::instance()->get('add_error'));
		endif;
	}



	public function edit($offer_id = NULL, $category_id = NULL){

		if(!$this->isLoggedIn()) return $this->index($offer_id);

		if(!$this->hasAccess('notbanned')) return;

		if(empty($offer_id)) { Lib::pagenotfound(); return; }

		$this->offer = new Offer_Model($offer_id);

		if($this->offer->id == 0) { Lib::pagenotfound(); return; };

		if(!$this->offer->is_viewed_by_owner and !$this->hasAccess('moderator')) return $this->index($offer_id);

		if($this->offer->is_banned and !$this->hasAccess('moderator')) return;

		$this->EDITMODE = true;

		if (!empty($_POST) and empty($_POST['get_category']) and empty($_POST['get_region'])):

			if(!$this->save($offer_id)):
				$this->view->obj = $_POST;
			endif;

		else:

			$this->view = new View('offer/offer_edit_view');
			$this->title = 'Редактирование объявления';
			$this->template->titleInView = true;
			$this->noindex = true;
			$this->addJs('offer_edit.js?20131101');
			$this->offer->description = text::undecorate_urls($this->offer->description);

			$this->view->obj = $this->offer;

			if(empty($category_id)):
				if(!empty($_GET['id'])):
					$category_id = $_GET['id'];
				elseif(!empty($_POST)):
					if(!empty($_POST['category_id'])):
						$category_id = $_POST['category_id'];
					elseif(!empty($_POST['get_category'])):
						$this->errors->add('Выберите раздел из списка');
					endif;
					if(!empty($_POST['region_id'])):
						$this->view->region_id = $_POST['region_id'];
					elseif(!empty($_POST['get_region'])):
						$this->errors->add('Выберите регион из списка');
					endif;
				endif;
			endif;

			if(!empty($category_id)):
				if(preg_match('/^\d+$/', $category_id)):
					$category = ORM::factory('category', $category_id);
				else:
					$category = ORM::factory('category')->find(array('codename' => $category_id));
				endif;
			endif;

			if(!empty($category) and $category->id != 0) $this->view->category = $category;

		endif;
	}

	private function save($offer_id = NULL){


//*
		try{
//*/

			if (! empty($_POST)):

				if(!empty($_POST['id']) and empty($this->EDITMODE)):
					return $this->index($offer_id);
				endif;

/*

				text::print_r(array_merge($_POST, $_FILES));				exit;
	*/

				$_POST = new Validation(array_merge($_POST, $_FILES));

				if($this->EDITMODE):
					$_POST->add_rules('id','required');

					if($this->isAgent() and !empty($_POST->category_id)):
						$category = new Category_Model($_POST->category_id);
					else:
						if(isset($_POST->category_id)) unset($_POST->category_id);
						$category = &$this->offer->category;
						if(!$category->id || $category->has_children)
						{
							$_POST->add_rules('category_id', 'required');
							unset($category);
						}

					endif;

				else:

					$_POST
					->add_rules('category_id', 'required')
					->add_rules('type_id', 'required')
					;

					if(!empty($_POST['category_id'])) $category = new Category_Model($_POST['category_id']);

				endif;

				if(!empty($_POST['region_id'])) $region = new Region_Model($_POST->region_id);

				$_POST->pre_filter('trim',true)
					->pre_filter('strip_tags',true)

					->add_rules('region_id', 'required')

					->add_rules('period', 'required', array('valid', 'numeric'))

					->pre_filter('format::rawmoney', 'price', 'price_to')
					->add_rules('price', 'length[1,12]', array('valid', 'numeric'))
					->add_rules('price_to', 'length[1,12]', array('valid', 'numeric'))
					->add_callbacks('price', array($this, 'check_price'))

					->add_rules('description', 'required', 'length[2,2800]')
					->post_filter('utf8::ucfirst','title','description')

					->pre_filter('text::break_long_words','title')

					;



				if(!empty($category)):

					foreach($category->properties as $item):
						$name = 'property['.$item->id.']';

						if(!empty($item->required)) $_POST->add_rules($name, 'required');

						if(!empty($item->maxlength))
						{
							if(empty($item->list_id))
							{
								$_POST->add_rules($name, 'length['.( (string) $item->minlength).','.( (string) $item->maxlength) .']');
							}
							elseif(!empty($_POST['property'][$item->id]) and is_array($_POST['property'][$item->id]) and !empty($_POST['property'][$item->id]['other']))
							{
								$_POST->add_rules($name.'[other]', 'length['.( (string) $item->minlength).','.( (string) $item->maxlength) .']');
							}
						}
						else
						{
							$_POST->pre_filter('text::break_long_words',$name);
						}
						switch($item->datatype):
							case 'integer':
								$_POST->add_rules($name, array('valid','digit'));
							break;
							case 'decimal':
								$_POST->add_rules($name, array('valid','numeric'));
							break;
							case 'date':
							case 'datetime':
							case 'year':
							case 'phone':
								$_POST->add_rules($name, array('valid',$item->datatype));
							break;
						endswitch;

					endforeach;

/**
 * Проверяем есть ли районы и метро у категории
 */

					if((!$category->has_district or !$region->has_district) and !empty($_POST->district_id)):
						$_POST->district_id = 0;
					elseif($category->has_district == 2):
						$_POST->pre_filter('intval', 'district_id');
						$_POST->add_rules('district_id', 'required');
					endif;

					if((!$category->has_subway  or !$region->has_subway) and !empty($_POST->subway_id)):
						$_POST->subway_id = 0;
					elseif($category->has_subway == 2):

						$_POST->pre_filter('intval', 'subway_id');
						$_POST->add_rules('subway_id', 'required', array('valid','digit'));
					endif;



					if(!$category->autotitle)
					{
						$_POST->add_rules('title', 'length[2,76]');
					};

				endif;

				if(!$this->isLoggedIn()):
					$_POST->add_rules('user_email','required',array('valid','email'))
						->add_rules('user_phone',array('valid','phone'))
						->add_rules('user_password','required','length[4,32]')
						->add_rules('user_repeat_password','required','length[4,32]')
						->add_rules('user_name','length[2,64]')
						->add_callbacks('user_email', array($this, 'unique_email'))
						->add_callbacks('user_password', array($this, 'check_password'))
						->add_callbacks('user_accept_disclaimer', array($this, 'is_agreed'))
						->post_filter('format::rawphone','user_phone')
						->pre_filter('utf8::ucfirst','user_name')
						->post_filter('strtolower', 'user_email')
						;

					if(empty($_POST->user_role) or !in_array($_POST->user_role, array('general','company'))):
						$_POST->user_role = 'general';
					endif;

				else:

					$_POST->add_rules('custom_phone',array('valid','phone'))
						->post_filter('format::rawphone','custom_phone')
					;

				endif;

//				if($this->isModerator() and (!empty($_POST->has_not_user) or (@$this->EDITMODE and $this->offer->has_not_user))):
				if($this->isAgent() and (!empty($_POST->has_not_user) or (@$this->EDITMODE and $this->offer->has_not_user))):

					$_POST->add_rules('email',array('valid','email'))
						->add_rules('name','length[2,64]')
						->add_rules('phone',array('valid','phone'))
						->post_filter('format::rawphone','phone')
						->add_callbacks('phone', array($this, 'contact_required'))
						->pre_filter('utf8::ucfirst','name')
						->post_filter('strtolower', 'email')
					;

					$_POST->email_status = !empty($_POST->email_status)?'enabled':'disabled';

				else:

					$_POST->name = '';
					$_POST->email = '';
					$_POST->email_status = 'disabled';
					//$_POST->phone = '';

					if(!$this->EDITMODE and (!$this->isAgent() and (!$this->isLoggedIn() or !$this->user->offers_count))):
						$_POST->add_rules('captcha_code', 'required')
							->add_callbacks('captcha_code', array($this, 'check_captcha'));
					endif;
				endif;


/**
 * Обработка заголовка
 */
				if(!empty($category) and $category->autotitle){

					$autotitleformat = false;
					$autotitlevar = array();

					if(!$autotitleformat = ORM::factory('title_format')->where(array($category->foreign_key() => $category->id, 'type_id' => @$_POST->type_id))->find()->format)
					{

						$autotitleformat = ORM::factory('title_format')
							->where(array($category->foreign_key() => $category->id, 'type_id' => 0))
							->find()->format;
					}

				}

/**
 * Обработка доп. данных
 */
				if(!empty($autotitleformat))
				{
					if(substr_count($autotitleformat, 'region') && !empty($_POST['region_id']))
					{
						$autotitlevar['region'] = $region->title;
					}

					if(substr_count($autotitleformat, 'district') && !empty($_POST['district_id']))
					{
						$district = new District_Model($_POST->district_id);
						$autotitlevar['district'] = $district->title;
					}

					if(substr_count($autotitleformat, 'subway') && !empty($_POST['subway_id']))
					{
						$subway = new Subway_Model($_POST->subway_id);
						$autotitlevar['subway'] = $subway->title;
					}
				}


				$property_value = array();
				$property_count = 0;

				if(!empty($_POST->property)):

					$propertyList = ORM::factory('property')->in('id', array_keys($_POST->property))->find_all()->as_id_array();

					foreach($_POST->property as $id => $values):

						if(!isset($values)) continue;

/**
 * Внесение массива дополнительных данных
 */
						$othervalue = '';

						if(!is_array($values)):

							$values = array($values);

						elseif(!empty($values['other'])):

							$othervalue = $values['other'];
							unset($values['other']);

						endif;

						foreach($values as $i => $value):

							if($value == 'other') $value = $othervalue;

							if($value == '') continue;

							$property_value[$property_count] = array();
							$property_value[$property_count]['id'] = $id;

							if($propertyList[$id]->datatype == 'string' and $propertyList[$id]->list_id == 0):
								$property_value[$property_count]['value'] = text::ucSentence(text::capsFix($value));
								$property_value[$property_count]['value'] = str_replace('_', ' ', $property_value[$property_count]['value']);
							else:
								$property_value[$property_count]['value'] = $value;
							endif;

							if(!empty($autotitleformat) and $propertyList[$id]->codename){
								$autotitlevar[$propertyList[$id]->codename] = $property_value[$property_count]['value'];
								if(!empty($propertyList[$id]->units)) $autotitlevar[$propertyList[$id]->codename] .= ' ' . $propertyList[$id]->units;
							}

							$property_count++;
						endforeach;

					endforeach;
				endif;


/**
 * Auto title
 */
				if(!empty($autotitleformat)){

					if(count(@$autotitlevar)){

						$_POST->title = $this->_makeAutoTitle($autotitleformat, $autotitlevar);

					}else{
						$_POST->add_error('title', 'Недостаточно данных для формирования автозаголовка');
					}

				}else{

					$temptitle = text::removeBeginEndPunctuation($_POST->title);
					if(text::replace_words($temptitle)):

						$temptitle = text::removeBeginEndPunctuation($temptitle);

						if(empty($temptitle)):
							$_POST->add_error('title', 'Заголовок объявления некорректен.<br>
							Возможные причины:<br>
							- Лишнее указание типа объявления (куплю, продам и т.д.)<br>
							- Использование слов: срочно, дёшево, дорого и т.п.<br>');
						else:
							$textError[] = '<b>Внимание!</b> Заголовок объявления был автоматически откорректирован.<br>
							Возможные причины:<br>
							- Лишнее указание типа объявления (куплю, продам и т.д.)<br>
							- Использование слов: срочно, дёшево, дорого и т.п.<br>';
						endif;

					else:
						$_POST->add_rules('title', 'required', 'length[2,76]');
					endif;

					if(!empty($temptitle)) $_POST->title = $temptitle;

				}



/**
 * Проверка
 */

				if ($_POST->validate()):
/**
 * Очищаем тексты
 */
					$_POST->title = str_replace('_', ' ', $_POST->title);
					if(empty($autotitleformat)){
						$_POST->title = text::ucSentence(text::capsFix($_POST->title));
						$_POST->title = text::removeBeginEndPunctuation(text::typographyString($_POST->title));
					}

					$_POST->description = text::capsFix($_POST->description, TRUE);
					$_POST->description = text::ucSentence($_POST->description);
					$_POST->description = text::wordwrap_decorate_urls(text::typography($_POST->description));
					$_POST->short_description = text::limit_chars(text::untypography(strip_tags($_POST->description)), 110, '...', TRUE);

					if(!empty($textError)) Session::instance()->set('warning', join('<br>', $textError));


					switch(@$_POST->price_type):
						case 'negotiated':
							$_POST->price = $_POST->price_to = $_POST->currency = '';
						break;
						case 'fixed':
							$_POST->price_to = '';
						break;
					endswitch;



/**
 * Регистрируем нового пользователя
 */

					if(!$this->isLoggedIn()):

						$user = new User_Model;

						$user->email = $_POST->user_email;
						$user->email_status = !empty($_POST->user_email_status)?'enabled':'disabled';
						$user->phone = $_POST->user_phone;
						$user->name = $_POST->user_name;
						$user->role = $_POST->user_role;

						if($user->save()):

							$user_activation = new User_Activation_Model;
							$user_activation->{$user->foreign_key()} = $user->id;
							$user_activation->save();

							$user_password = new User_Password_Model;
							$user_password->setPassword($_POST['user_password']);
							$user_password->{$user->foreign_key()} = $user->id;
							$user_password->save();

							// пользователь добавлен, высылаем письмо с активационным ключом

							$activation_data = array(
								'email' => $user->email,
								'name' => $user->contact_name,
								'user_id' => $user->id,
								'activation_key' => $user_activation->activation_key
							);

							Auth::instance()->sendActivationEmail($activation_data);

							Auth::instance()->processUserLogin($user);
							Auth::instance()->setCookie('just_registered', $user->id, false, $expires_x_days = 3);

						endif;

					else:

						$user = $this->user;

/**
 * Проверка объявления на дубликат
 */
						if(!$this->EDITMODE and empty($autotitleformat)):

							$checkOffer = ORM::factory('offer')
							->where(array(
								'title' => $_POST->title,
								'user_id' => $user->id,
								'category_id' => $_POST->category_id,
								'type_id' => $_POST->type_id,
							))->find();


							if(!empty($checkOffer->id)):
								$timeDiff = date::timespan(strtotime($checkOffer->added), NULL, 'minutes,seconds');

								if($timeDiff['minutes'] == 0 && $timeDiff['seconds'] < 30):


									if(!empty($_POST->save_and_continue)):
										$this->data['save_and_continue'] = true;
									endif;

									$this->messages->add('Объявление добавлено!');

									$this->redirect = $this->offer->url_add_success;

									unset($this->offer);

									return;


								elseif($timeDiff['minutes'] < Lib::config('app.offer_is_dublicate_days')):
									$this->errors->add('Вы не можете размещать одинаковые объявления!');
									return;
								endif;

							endif;

						endif;


					endif; // is logged in



					if(empty($this->offer)) $this->offer = new Offer_Model;



					if($this->EDITMODE):
						if($_POST->title == $this->offer->title):
							unset($_POST->title);
						else:
							$importantContentChanged = true;
						endif;
						if($_POST->description == $this->offer->description):
							unset($_POST->description);
							unset($_POST->short_description);
						else:
							$importantContentChanged = true;
						endif;
					endif;


/**
 * checking phone
 */
 					if(empty($_POST->has_not_user) and !$this->offer->has_not_user):
						if(!empty($_POST->custom_phone)
//							and (!$this->EDITMODE or $this->user->id == $this->offer->user_id)
							and (empty($this->user->phone) or $_POST->custom_phone != $this->user->phone)):

								$_POST->phone = $_POST->custom_phone;

/*								if(empty($this->user->phone)):
									$this->user->phone = $_POST->custom_phone;
									$this->user->save();
								endif;
*/
						else:
							$_POST->phone = '';
						endif;
					endif;


					$this->offer->setValuesFromArray($_POST);


					if($this->EDITMODE and strtotime($this->offer->expiration) > time()):
						$this->offer->expiration = date::getForDb(strtotime($this->offer->expiration . ' +'.$_POST->period.' days'));
					else:
						$this->offer->expiration = date::getForDb(strtotime('+'.$_POST->period.' days'));
					endif;


					if(!$this->EDITMODE) $this->offer->{$user->foreign_key()} = $user->id;


					if(!$this->EDITMODE):
						if($this->isLoggedIn() and !$this->isNotActivated()):
							$this->offer->status = 'disabled';
						else:
							$this->offer->status = 'disabled';
						endif;
					endif;

					if($this->isModerator()):
						$this->offer->checked = 1;
					endif;

					if($this->offer->is_viewed_by_owner):
					endif;

					if(!$this->EDITMODE):
						$this->offer->ip_added = Input::instance()->ip_address();
						$this->offer->save();
					elseif($this->offer->is_viewed_by_owner):
						$this->offer->ip_updated = Input::instance()->ip_address();
					endif;


/**
 * Добавление доп. данных
 */
					$dataCount = 0;

					foreach($property_value as $values):

						$id = $values['id'];
						$value = $values['value'];

						if($this->EDITMODE and isset($this->offer->datas[$dataCount])):

							$data = &$this->offer->datas[$dataCount];
							$dataCount++;

							if($data->property_id == $id && $data->datavalue == $value):
								continue;
							endif;

							$importantContentChanged = true;

						else:

							$data = new Data_Model;
							$data->{$this->offer->foreign_key()} = $this->offer->id;

						endif;

						$data->property_id = $id;
						$data->datavalue = $value;

						$data->save();

					endforeach;

					if($this->EDITMODE):
						while(isset($this->offer->datas[$dataCount])):
							$this->offer->datas[$dataCount]->delete();
							$dataCount++;
						endwhile;
					endif;


/**
 * IMAGES
 */
					$imagesAmount = 0;
					$imagecount = 1;

					if(!isset($_POST->mainimage)) $_POST->mainimage = 0;

					if($this->EDITMODE):

						if(!empty($_POST->deleteimage)):
							foreach($_POST->deleteimage as $key => $del):
								ORM::factory('picture', $del)->where($this->offer->foreign_key(), $this->offer->id)->delete();
							endforeach;
						endif;

						if($imagesAmount = $this->offer->pictures->count()):

							foreach($this->offer->pictures as $key => $picture):
								if($key == $_POST->mainimage):
									$picture->priority = 0;
									$picture->save();
								else:
									if($picture->priority != $imagecount):
										$picture->priority = $imagecount;
										$picture->save();
									endif;
									$imagecount++;
								endif;

							endforeach;
						endif;

					endif;

					if($imagesAmount < Lib::config('picture.offer', 'max_amount') and count(Lib::config('picture.offer', 'folder')) and !empty($_POST->image) and !empty($_POST->image['name']) and count($_POST->image['name'])):

						$imageError = array();

						foreach($_POST->image['name'] as $key => $value):

							if(empty($value)) continue;
							if($imagecount > Lib::config('picture.offer', 'max_amount')) break;

							$imagetitle = '&laquo;Изображение №'.($key+1).'&raquo;';

							if(!$error = valid::image('offer', $_POST->image, $key, $imagetitle)):

								$picture = new Picture_Model;
								$picture->mode = 'offer';

								$picture->{$this->offer->foreign_key()} = $this->offer->id;

								if(!empty($_POST->image_title) and !empty($_POST->image_title[$key])) $picture->title = $_POST->image_title[$key];

								if($_POST->mainimage == ($key + $imagesAmount)):
									$picture->priority = 0;
								else:
									$picture->priority = $imagecount++;
								endif;

								$picture->new_file = $_POST->image['tmp_name'][$key];

								if(!$picture->save()):
									$imageError[] = 'Не удалось сохранить ' . $imagetitle;
								endif;
							else:
								$imageError[] = $error;
							endif;

						endforeach;

						if(count($imageError)):
							Session::instance()->set('add_error', join('<br>', $imageError).($this->EDITMODE?'<br>Обратитесь к Администрации сайта':'<br>Вы сможете исправить это в режиме редактирования.'));
						endif;

						$importantContentChanged = true;

					endif;


					if($this->EDITMODE):

						if(!$this->isModerator() and !empty($importantContentChanged)) $this->offer->checked = 0;

						$this->offer->save();

						$this->messages->add('Объявление обновлено!');

						Session::instance()->set('offer_edited', $this->offer->id);

						$this->redirect = $this->offer->url;

					else:


						$this->messages->add('Объявление добавлено!');

						if(!empty($_POST->save_and_continue)):
							$this->data['save_and_continue'] = true;
						else:
							if(!$this->isLoggedIn() or $this->isNotActivated()):
								$this->redirect = $this->offer->url_add_success;
							else:
								Session::instance()->set('offer_added', $this->offer->id);
								$this->redirect = $this->offer->url;//_add_success;
							endif;
						endif;

					endif;

					unset($this->offer);

					return true;

				else: // is valid
					$this->errors->add($_POST->list_errors());
				endif; //is valid

			endif;// POST
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
	}


	public function quickedit(){

		if(!$this->hasAccess('moderator')) return;

		try{
// */
			if (!empty($_POST)){

				if(empty($_POST['quickedit_id'])):
					Lib::pagenotfound(); return;
				elseif(!$this->mainobj = ORM::factory($this->modelName, $_POST['quickedit_id'])):
					Lib::pagenotfound(); return;
				endif;
//*

				$_POST = new Validation($_POST);

				$_POST->add_rules('quickedit_id','required')

					->pre_filter('trim',true)
					->pre_filter('strip_tags',true)
				;

				if(!empty($_POST->title)):
					$_POST->add_rules('title', 'length[2,76]')
					->pre_filter('text::break_long_words','title')
					->post_filter('utf8::ucfirst','title')
					;
				endif;

				if(!empty($_POST->description)):
					$_POST->add_rules('description', 'required', 'length[2,2800]')
					->post_filter('utf8::ucfirst','description')
					;
				endif;

				if(!empty($_POST->price_type)):
					$_POST

					->pre_filter('format::rawmoney', 'price', 'price_to')
					->add_rules('price', 'length[1,12]', array('valid', 'numeric'))
					->add_rules('price_to', 'length[1,12]', array('valid', 'numeric'))
					->add_callbacks('price', array($this, 'check_price'))

					;
				endif;

				if ($_POST->validate()):

					$this->data['id'] = $this->mainobj->id;

					if(!empty($_POST->title)):

						$_POST->title = text::ucSentence(text::capsFix($_POST->title));
						$_POST->title = text::removeBeginEndPunctuation(text::typographyString($_POST->title));

						$this->data['name'] = 'title';

						if($_POST->title == $this->mainobj->title):
							unset($_POST->title);
						else:
							$this->data['value'] = $_POST->title;
							$contentChanged = true;
						endif;

					endif;

					if(!empty($_POST->description)):
						$_POST->description = text::ucSentence(text::capsFix($_POST->description, TRUE));
						$_POST->description = text::wordwrap_decorate_urls(text::typography($_POST->description));
						$_POST->short_description = text::limit_chars(text::untypography(strip_tags($_POST->description)), 110, '...', TRUE);

						$this->data['name'] = 'description';

						if($_POST->description == $this->mainobj->description):
							unset($_POST->description, $_POST->short_description);
						else:
							$this->data['value'] = $_POST->description;
							$contentChanged = true;
						endif;
					endif;

					if(!empty($_POST->price_type)):

						$this->data['name'] = 'price';

						switch(@$_POST->price_type):
							case 'negotiated':
								$_POST->price = $_POST->price_to = $_POST->currency = '';
							break;
							case 'fixed':
								$_POST->price_to = '';
							break;
						endswitch;

						if($_POST->price_type != $this->mainobj->price_type or $_POST->price != $this->mainobj->price or $_POST->price_to != $this->mainobj->price_to or $_POST->currency != $this->mainobj->currency):
							$contentChanged = true;
						endif;
					endif;

					if(!empty($contentChanged)):

						$this->mainobj->setValuesFromArray($_POST);

						$this->mainobj->save();

					endif;

					if(!empty($_POST->price_type) and !empty($contentChanged)):

						$this->data['value'] = 'Цена: ' . ($this->mainobj->price_html ? $this->mainobj->price_html : '<span>не указана</span>');
						$this->data['title'] = $this->mainobj->price_type.'|'.$this->mainobj->currency.(!empty($this->mainobj->price_to)?'|1':'');

					endif;

				else:

					$this->errors->add($_POST->list_errors());

				endif;
			}
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
// */
	}


}
/* ?> */
