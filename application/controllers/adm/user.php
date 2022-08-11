<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Administration
 * Users Controller.
 */
class User_Controller extends AdmController {

	public function __construct(){

		parent::__construct();

		$this->model = new User_Model;

		$this->perpage = 16;
	}


	public function unique_email(Validation $v)
	{

		if(empty($v->email)) return true;

		$id = ORM::factory('user')->select('id')->find(array('email' => $v->email))->id;

		if($id == 0 or @$v->id == $id) return true;

		$v->add_error('email', 'email_exists');

		return false;
	}


	public function check_password(Validation $v){

		if(!empty($v->new_password) and $v->new_password != $v->repeat_password):
			$v->add_error('new_password', '"new_password" не может быть пустым и должен совпадать с подтверждением');
			return false;
		endif;

		return true;

	}


	public function index($id = NULL, $page = 0){

		$this->view = new View('adm/user_view');
//		$this->parent_title = 'Настройки';
		$this->title = 'Управление пользователями';

		$this->objecttitle = 'Пользователь';

		$this->addJs('adm_user.js?20131108');

		if (! empty($_POST)){

			if (!empty($_POST['id'])):

				$this->obj = new $this->model(@$_POST['id']);

				if(!empty($_POST['delete'])):

					if(!$this->isAdministrator()):
						$this->errors->add('У Вас нет прав для удаления пользователя');
					elseif($this->obj->delete()):
						$this->messages->add($this->objecttitle . ' успешно удален');
						$this->data['act'] = 'delete';
					endif;

				else:

					$_POST = new Validation($_POST);

						$_POST->pre_filter('trim',true)
/**
* USER DATA
*/
						->pre_filter('strtolower', 'email', 'secondary_email')
						->add_rules('email','required', array('valid','email'))
						->add_callbacks('email', array($this, 'unique_email'))
						->add_rules('secondary_email', array('valid','email'))
						->pre_filter('utf8::ucfirst','name')
						->add_rules('name','length[2,45]')

						->post_filter('format::rawphone','phone')
						->add_rules('address', 'length[2,255]')
						->add_rules('reference_point', 'length[2,255]')
						->add_rules('website','length[8,255]',array('valid','url'))
						->pre_filter('format::url','website')

						->add_rules('new_password', 'length[4,32]')
						->add_rules('repeat_password','length[4,32]')
						->add_callbacks('new_password', array($this, 'check_password'))

						->add_rules('discount', 'length[1,2]', array('valid','digit'))

//                        ->add_rules('certificate_num', array('valid', 'alpha_dash'))
                        ->add_rules('certificate_dt', array('valid', 'date'))
//
//                        ->add_rules('license_num', array('valid', 'alpha_dash'))
                        ->add_rules('license_dt', array('valid', 'date'))

					;

					$is_valid = $_POST->validate();


					$this->obj->name = @$_POST->name;

					if($this->obj->email != @$_POST->email) $emailChanged = true;
					$this->obj->email = @$_POST->email;
					$this->obj->secondary_email = @$_POST->secondary_email;

					$this->obj->region_id = @$_POST->region_id;

					if($this->isAdministrator()):
						$this->obj->role = @$_POST->role;
					endif;

					$this->obj->phone = @$_POST->phone;
					$this->obj->address = @$_POST->address;
					$this->obj->reference_point = @$_POST->reference_point;
					$this->obj->website = @$_POST->website;
					$this->obj->discount = @$_POST->discount;

					if($this->obj->discount > Lib::config('payment.maximum_discount')):
						$this->errors->add('Скидка не может быть более '.Lib::config('payment.maximum_discount').'%!');
						$is_valid = false;
					endif;


					if ($is_valid){

						$this->obj->setUpdated();

						$objOldStatus = $this->obj->status;

						if(!empty($_POST->status) and @$_POST->status != 'disabled' and $this->obj->status != @$_POST->status
							and (!$this->obj->is_administrator or $this->isAdministrator())):


/* активация */				if($objOldStatus == 'disabled' and $_POST->status == 'enabled'):
								if(!empty($_POST->update_obj_status)):
									$this->obj->setActivated();
								else:
									$this->obj->status = 'enabled';
									if($this->obj->user_activation) $this->obj->user_activation->delete();
								endif;

/* блокировка */			elseif($objOldStatus != 'banned' and $_POST->status == 'banned'):
								$this->obj->setBanned($this->user->id);

/* разблокировка */			elseif($objOldStatus == 'banned' and $_POST->status == 'enabled'):
								$this->obj->unBan();

							endif;

						endif;

						if($this->obj->save()):

							if($this->obj->status == 'enabled' and $objOldStatus == 'disabled' and !empty($_POST->update_offer_status)):
								foreach($this->obj->offers as $offer){
									if($offer->status == 'disabled'):
										$offer->expiration = date::getForDb(time() + (strtotime($offer->expiration) -  strtotime($offer->added)));
										$offer->added = $offer->positioned = date::getForDb();

										$offer->enable();
									endif;
								}
							endif;

                            $certificate = array();
                            if (isset($_FILES['certificate_scan'])):
                                $certificate = $this->obj->user_certificate->check_uploaded_file($_FILES['certificate_scan'], $this->obj, 'certificate', $delete_old_files = TRUE);
                                if (isset($certificate['errors']) && !empty($certificate['errors'])):
                                    $this->errors->add($certificate['errors']);
                                    unset($certificate['errors']);
                                endif;
                            endif;

                            $license = array();
                            if (isset($_FILES['license_scan'])):
                                $license = $this->obj->user_certificate->check_uploaded_file($_FILES['license_scan'], $this->obj, 'license', $delete_old_files = TRUE);
                                if (isset($license['errors']) && !empty($license['errors'])):
                                    $this->errors->add($license['errors']);
                                    unset($license['errors']);
                                endif;
                            endif;

                            $other_doc = array();
                            if (isset($_FILES['other_doc'])):
                                $other_doc = $this->obj->user_certificate->check_uploaded_file($_FILES['other_doc'], $this->obj, 'other', $delete_old_files = TRUE);
                                if (isset($other_doc['errors']) && !empty($other_doc['errors'])):
                                    $this->errors->add($other_doc['errors']);
                                    unset($other_doc['errors']);
                                endif;
                            endif;

                            // данные возвращаемые в ответ на AJAX-запрос
                            $this->data['user_certificate'] = array(
                                'file' => isset($certificate['path'])?substr($certificate['path'], strlen($_SERVER['DOCUMENT_ROOT'])):(string)@$this->obj->user_certificate->certificate,
                                'dt' => date::getForDb(),
                            );
                            $this->data['user_license'] = array(
                                'file' => isset($license['path'])?substr($license['path'], strlen($_SERVER['DOCUMENT_ROOT'])):(string)@$this->obj->user_certificate->license,
                                'dt' => date::getForDb(),
                            );
                            $this->data['user_other'] = array(
                                'file' => isset($other_doc['path'])?substr($other_doc['path'], strlen($_SERVER['DOCUMENT_ROOT'])):(string)@$this->obj->user_certificate->other,
                                'dt' => date::getForDb(),
                            );
                            $this->data['obj_id'] = $this->obj->id;

                            $certificate_data = array(
                                'dt' => date::getForDb(),
                                'st' => 'Y',
                            );

                            if (!empty($certificate)):
                                $certificate_data['certificate'] = substr($certificate['path'], strlen($_SERVER['DOCUMENT_ROOT']));
                            endif;
                            if (!empty($_POST->certificate_num)) $certificate_data['certificate_num'] = $_POST->certificate_num;
                            if (!empty($_POST->certificate_dt)) $certificate_data['certificate_dt'] = date::getDateForDb($_POST->certificate_dt);

                            if (!empty($license)):
                                $certificate_data['license'] = substr($license['path'], strlen($_SERVER['DOCUMENT_ROOT']));
                            endif;
                            if (!empty($_POST->license_num)) $certificate_data['license_num'] = $_POST->license_num;
                            if (!empty($_POST->license_dt)) $certificate_data['license_dt'] = date::getDateForDb($_POST->license_dt);

                            if (!empty($other_doc)):
                                $certificate_data['other'] = substr($other_doc['path'], strlen($_SERVER['DOCUMENT_ROOT']));
                            endif;

                            if (isset($certificate_data['certificate']) || isset($certificate_data['certificate_num']) || isset($certificate_data['certificate_dt'])
                                    || isset($certificate_data['license']) || isset($certificate_data['license_num']) || isset($certificate_data['license_dt'])
                                    || isset($certificate_data['other']))
                            {
                                if ($this->obj->user_certificate->loaded):
                                    $this->obj->user_certificate->setValuesFromArray($certificate_data);
                                    $this->obj->user_certificate->save();
                                else:
                                    $certificate_data['user_id'] = $this->obj->id;
                                    $user_certificate = new User_Certificate_Model;
                                    $user_certificate->setValuesFromArray($certificate_data);
                                    $user_certificate->save();
                                endif;
                            }

							$this->messages->add( 'Данные успешно сохранены');

						endif;

/**
 * Смена пароля
 */
						if(!empty($_POST->new_password)):
							$this->obj->user_password->setPassword($_POST['new_password']);
							$this->obj->user_password->save();
							$this->messages->add('Пароль успешно изменён!');
						endif;

						if(empty($_POST->save) or request::is_ajax()):

							if($this->obj->status != $objOldStatus) $this->data['act'] = $objOldStatus;
							unset($this->obj);

						endif;
						if(!empty($_POST->save)):
							$this->data['act'] = 'save';
						endif;



					}else{
						$this->errors->add($_POST->list_errors());
						$this->view->obj = $this->obj;
					}

				endif;
			else: // empty id
				$this->errors->add('ID пользователя не указан!');
			endif;

		}elseif(!empty($id)){

			$this->obj = new $this->model($id);

			if(!$this->isAdministrator() and $this->obj->is_moderator):

				unset($this->obj);
				$this->errors->add('У вас нет прав для этой операции');

			else:

				switch(@$this->obj->gender):
					case 'male':
						$this->obj->gender = 'мужчина';
					break;
					case 'female':
						$this->obj->gender = 'женщина';
					break;
					default:
						$this->obj->gender = 'не указан';
					break;
				endswitch;

				$this->obj->registered = date::getLocalizedDate($this->obj->registered);
				$this->obj->simple_last_activity;
				$this->obj->offers_count;
				$this->obj->bonus_amount;
				$this->obj->url;
				$this->obj->url_add_bonus;

                if ($this->obj->user_certificate->loaded):
                    $this->data['user_certificate'] = array(
                        'file' => (string)$this->obj->user_certificate->certificate,
                        'num' => $this->obj->user_certificate->certificate_num,
                        'dt' => $this->obj->user_certificate->certificate_dt,
                    );
                    $this->data['user_license'] = array(
                        'file' => (string)$this->obj->user_certificate->license,
                        'num' => $this->obj->user_certificate->license_num,
                        'dt' => $this->obj->user_certificate->license_dt,
                    );
                    $this->data['user_other'] = array(
                        'file' => (string)$this->obj->user_certificate->other,
                    );
                endif;
			endif;

		}

		$this->view->mode = '';

		if(!empty($_REQUEST['q'])):
			$this->view->q = $_REQUEST['q'];
		elseif(!empty($_REQUEST['mode'])):
			$this->view->mode = $_REQUEST['mode'];
		else:
			$this->view->mode = 'enabled';
		endif;

		$this->view->list_page = $page;

		if($this->view->mode != 'productive'):

			$this->setFilters(true);

			$countTotal = $this->model->count_all();

			$paginationConfig = array(
				'total_items'    => $countTotal, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
				'item_title'		=> array('пользователь','пользователя', 'пользователей'),
			);

			$pagination = new Pagination($paginationConfig);

			$this->view->pagination = @$pagination;

			$this->setFilters();
			$this->view->userList = $this->model->find_all($this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);

		else:

			$this->perpage = 50;

			$this->setFilters();
			$this->view->userList = $this->model->find_all($this->perpage);

		endif;

		if(request::is_ajax() and empty($id) and (empty($_POST) or !empty($emailChanged) or !empty($_POST['delete'])) ):

			if(!empty($this->view->q)):
				$this->data['q'] = @$this->view->q;
			else:
				$this->data['mode'] = @$this->view->mode;
			endif;

			$this->data['list'] = $this->view->userList->as_links_array($this->view->mode == 'productive'?array(2 => 'offers_count'):NULL);

			if(!empty($pagination)) $this->data['pages'] = $pagination->render();

		endif;

	}

	private function setFilters($forcount = FALSE){

		if(!empty($this->view->q)):
			$this->model->orlike(array('email' => $this->view->q, 'name' => $this->view->q));
		elseif(!empty($this->view->mode)):
			switch($this->view->mode):
				case 'realtor':
				case 'agent':
				case 'company':
				case 'legal':
				case 'general':
					$this->model->where('role', $this->view->mode);
				break;
				case 'moderator':
					$this->model->in('role', array('moderator','administrator'));
				break;
				case 'productive':
					$this->model->select('count(offers.id) as offers_count, users.id, users.email');
					$this->model->orderby('offers_count', 'desc');
					$this->model->where('users.status','enabled');
					$this->model->join('offers', 'users.id', 'offers.user_id', 'LEFT');
					$this->model->groupby('users.id');
				break;
				case 'banned':
				case 'disabled':
				default:
					$this->model->where('status',$this->view->mode?$this->view->mode:'enabled');
				break;
			endswitch;
		endif;
	}

	/**
 * Set Checked
 */
	public function check($obj_id = NULL, $remove = FALSE){

		if(!$this->isModerator()) return Lib::pagenotfound();

		if($obj_id == NULL) { Lib::pagenotfound(); return; }

		$this->mainobj = ORM::factory('user', $obj_id);

		if($this->mainobj->id == 0)  { Lib::pagenotfound(); return; }

		if(!$remove):
			if($this->mainobj->setChecked()):
				$this->title = 'Пользователь проверен!';
			endif;
		else:
			if($this->mainobj->unCheck()):
				$this->title = 'Проверка пользователя отменена !';
			endif;
		endif;

	}

	public function uncheck($obj_id = NULL){
		return $this->check($obj_id, TRUE);
	}

/**
 * Блокировка
 */
	public function ban($obj_id = NULL, $remove = FALSE){

		if(!$remove and empty($_POST)) $this->returnViewInAjax = true;

		if(!$this->isModerator()) return Lib::pagenotfound();

		if($obj_id == NULL) { Lib::pagenotfound(); return; }

		$this->mainobj = ORM::factory('user', $obj_id);

		if($this->mainobj->id == 0)  { Lib::pagenotfound(); return; }

		if($this->mainobj->is_moderator and !$this->hasAccess('administrator')):

			unset($this->obj);
			return;

		endif;

		$this->data['id'] = $this->mainobj->id;

		if($remove):

			if($this->mainobj->unBan()):
				if($this->mainobj->email != ''):

					$message_data = array(
						'email' => $this->mainobj->email,
//						'title' => text::untypography($this->mainobj->fulltitle),
					);

					$message_tpl = 'adm/user/email/unban_view';
					$subject = 'Ваша учетная запись разблокирована!';

					Lib::sendEmail($subject, $message_tpl, $message_data);
				endif;

				$this->messages->add('Учетная запись разблокирована!');

				$this->redirect = $this->mainobj->url_unban_success;
				return;
			endif;

		else:

	//*
			try{
	//*/
				if (! empty($_POST)):

					if(!empty($_POST['cancel'])):
						$this->redirect = $this->mainobj->url;
						return;
					else:

						$_POST = new Validation($_POST);

						$_POST->pre_filter('trim',true)
							->pre_filter('strip_tags',true)
							->pre_filter('text::break_long_words',true)

	//						->add_rules('captcha_code', 'required')
	//						->add_callbacks('captcha_code', array($this, 'check_captcha'))
							->add_rules('content', 'length[2,500]')
							;

						if(@$_POST->predefined_reason == 'other'){
							$_POST->add_rules('content', 'required', 'length[2,500]');
						}

						if ($_POST->validate()):

								if(@$_POST->predefined_reason == 'other'){
									$reason = @$_POST->content;
								}else{
									$titlesarray = Lib::config('app.user_ban_reasons');

									$reason = $titlesarray[$_POST->predefined_reason];
								}

								if($this->mainobj->setBanned($this->user->id, text::typography($reason))):

									if($this->mainobj->email != ''):
										$message_data = array(
											'email' => $this->mainobj->email,
											//'title' => text::untypography($this->mainobj->fulltitle),
											'message_content' => @$reason,
											//'url_edit' => $this->mainobj->url_edit,
										);

										$message_tpl = 'adm/user/email/ban_view';
										$subject = 'Ваша учетная запись заблокирована!';

										Lib::sendEmail($subject, $message_tpl, $message_data);
									endif;


								endif;

								$this->messages->add('Учетная запись заблокирована!');

								$this->redirect =  $this->mainobj->url_ban_success;

								return true;

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

		$this->view = new View('adm/user/ban_view');

		$this->view->no_reason = false;//(!$this->user->is_moderator or $remove);
		$this->title = 'Блокировка пользователя';
		$this->template->titleInView = true;
		$this->noindex = true;

		$this->addJS('adm_moderate.js');

		$this->view->mainobj = $this->mainobj;

	}

	public function unban($obj_id = NULL){

		return $this->ban($obj_id, TRUE);

	}

	public function ban_success($obj_id = NULL){

		$this->title = 'Пользователь заблокирован!';
		$this->noindex = true;

	}

	public function unban_success($obj_id = NULL){

		$this->title = 'Пользователь разблокирован!';
		$this->noindex = true;

	}


/**
 * Удаление пользователя
 */

	public function delete($obj_id = NULL){

		if(empty($_POST)) $this->returnViewInAjax = true;

		if(!$this->hasAccess('administrator'))  return;

		if($obj_id == NULL) { Lib::pagenotfound(); return; }

		$this->mainobj = ORM::factory('user', $obj_id);

		if($this->mainobj->id == 0)  { Lib::pagenotfound(); return; }

//*
		try{
//*/
			if (! empty($_POST)):

				$this->data['id'] = $this->mainobj->id;

				if(!empty($_POST['cancel'])):
					$this->redirect = $this->mainobj->url;
					return;
				else:

					$_POST = new Validation($_POST);

					$_POST->pre_filter('trim',true)
						->pre_filter('strip_tags',true)
						->pre_filter('text::break_long_words',true)
						->add_rules('content', 'length[2,500]')
						;

					if ($_POST->validate()):

						if(@$_POST->predefined_reason != '' and $this->mainobj->email != ''):

							if(@$_POST->predefined_reason == 'other'):
								$reason = @$_POST->content;
							else:
								$reason = Lib::config('app.user_delete_reasons', $_POST->predefined_reason);
							endif;

							$message_data = array(
								'email' => $this->mainobj->email,
//								'title' => text::untypography($this->mainobj->fulltitle),
								'message_content' => @$reason,
							);

							$message_tpl = 'adm/user/email/delete_view';
							$subject = 'Ваша учетная запись удалена!';

							Lib::sendEmail($subject, $message_tpl, $message_data);

						endif;


						if($this->mainobj->delete(NULL, $this->user->id))
						{

							$this->redirect = $this->mainobj->url_delete_success;

							return;

						};

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
		$this->view = new View('adm/user/delete_view');
		$this->view->form_action = $this->mainobj->url_delete;
		$this->view->delete_mode = true;

		$this->addJS('adm_moderate.js');

		$this->title = 'Удаление учетной записи';
		$this->template->titleInView = true;
		$this->noindex = true;

		$this->view->mainobj = $this->mainobj;

	} // delete


	public function delete_success($obj_id = NULL){

		$this->title = 'Учетная запись удалена!';
		$this->noindex = true;

	}

    protected $_attached_file_codes = array(
        'certificate',
        'license',
        'other',
    );
    public function delete_cert_file($id, $file_code)
    {
        if (!in_array($file_code, $this->_attached_file_codes))
        {
            $this->errors->add('Неизвестный тип прикрепленного файла.');
            return;
        }

        $this->obj = new $this->model($id);
        $this->obj->user_certificate->remove_file($file_code, $this->obj);
        $this->obj->user_certificate->$file_code = '';
        if (in_array($file_code, array('certificate', 'license')))
        {
            $this->obj->user_certificate->{$file_code.'_num'} = '';
            $this->obj->user_certificate->{$file_code.'_dt'} = '';
        }
        $this->obj->user_certificate->save();

        switch ($file_code)
        {
            case 'certificate':
                $message = 'Скан "гувохномы" ';
            break;
            case 'license':
                $message = 'Скан лицензии ';
            break;
            case 'other':
                $message = 'Файл с прочими документами ';
            break;
        }
        $message .= 'успешно удален.';

        $this->messages->add($message);
    }
}
/* ?> */