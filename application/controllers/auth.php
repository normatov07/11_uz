<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Authentification Controller.
 */
class Auth_Controller extends Controller {

	public function __construct(){
		// die("Auth class");
		parent::__construct();
		$this->model = new User_Model;

		$this->addJs('auth.js?'.mt_rand());

	}

	public function unique_email(Validation $v)
	{
		if(empty($v->email)) return true;
		$this->model->select('id');
		$id = ORM::factory('user')->find(array('email' => $v->email))->id;

		if($id == 0 or (!empty($v->id) and $v->id == $id)) return true;

		$v->add_error('email', 'email_exists');

		return false;
	}

	public function is_agreed(Validation $v)
	{
		if(!empty($v->accept_disclaimer)) return true;
		$v->add_error('accept_disclaimer', 'agreed_with_disclaimer');

		return false;
	}


	public function check_password(Validation $v){

		if($v->password != $v->repeat_password):
			$v->add_error('password', 'password_confirm');
			return false;
		else:
			return true;
		endif;

	}

	public function register(){

        if ($this->isLoggedIn()) {
			$this->view = new View('auth/login_view');
            return;
        }
//*
		try{
//*/
			if (! empty($_POST)){

				$_POST = new Validation($_POST);

				//add rules, filters
				$_POST->pre_filter('trim',true)
					  ->pre_filter('strtolower', 'email')
					  ->add_rules('email','required',array('valid','email'))
					  ->add_callbacks('email', array($this, 'unique_email'))
					  ->add_rules('password','required','length[4,32]')
					  ->add_rules('repeat_password','required','length[4,32]')
					  ->add_rules('name','length[2,64]')
					  ->add_callbacks('password', array($this, 'check_password'))
					  ->add_callbacks('captcha_code', array($this, 'check_captcha'))
					  ->add_callbacks('accept_disclaimer', array($this, 'is_agreed'))
					  ;

				$is_valid = $_POST->validate();

				if ($is_valid):

					if(!in_array($_POST->role, array('general','company'))):
						$_POST->role = 'general';
					endif;

					$this->obj = new $this->model();
					$this->obj->setValuesFromArray($_POST);

					if($this->obj->save()):

						$user_activation = new User_Activation_Model;
						$user_activation->{$this->obj->foreign_key()} = $this->obj->id;
						$user_activation->save();

						$user_password = new User_Password_Model;
						$user_password->setPassword($_POST['password']);
						$user_password->{$this->obj->foreign_key()} = $this->obj->id;
						$user_password->save();

						// пользователь добавлен, высылаем письмо с активационным ключом

						$activation_data = array(
							'email' => $this->obj->email,
							'name' => $this->obj->contact_name,
							'user_id' => $this->obj->id,
							'activation_key' => $user_activation->activation_key
						);
// die("dfsdfds");
						// if (!Auth::instance()->sendActivationEmail($activation_data)) {

						// 	Lib::log('Не отправляется письмо об активации!');
						// 	throw new Kohana_Exception('Ошибка!'); // чтобы избежать дублирования сообщений об ошибках

						// }
						// die("dfgdf");
						Auth::instance()->processUserLogin($this->obj);
						Auth::instance()->setCookie('just_registered', $this->obj->id, false, $expires_x_days = 3);

						// $this->redirect = '/registration_success';
						header('Location: http://11.lo/registration_success');
						exit();

					endif;

				else://validate
					$this->errors->add($_POST->list_errors());
				endif;

			}
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->view = new View('auth/register_view');
		$this->title = 'Регистрация';

		if(!empty($this->obj)) $this->view->obj = $this->obj;
		if(!empty($_POST)) $this->view->accept_disclaimer = $_POST['accept_disclaimer'];


	}



	public function registration_success(){
		$this->template->dontDisplayNotActivatedWarning = true;
		$this->view = new View('auth/register_success_view');
		$this->title = 'Вы зарегистрированы!';
	}



	public function activate(){
		$this->template->dontDisplayNotActivatedWarning = true;
//*
		try{
//*/
			if (! empty($_GET) or !empty($_POST)){

				if(!empty($_GET)) $_POST = new Validation($_GET);
				else $_POST = new Validation($_POST);

				if(!empty($_POST['uid']) and $user = ORM::factory('user', $_POST['uid']) and $user->isActivated()):

						$this->errors->add('Вы уже активировали свой аккаунт'
						. (!$this->isLoggedIn() ? ', попробуйте <a href="/login/" onclick="$(\'#login\').click(); return false;">авторизироваться</a>.':'')
						. '<br> По возникшим вопросам <a href="/contacts/">пишите нам</a>.');

				elseif(isset($_POST['activation_key'])):

					//add rules, filters
					$_POST->pre_filter('trim',true)
	//					  ->add_rules('email','required',array('valid','email'))
						  ->add_rules('activation_key','required')
					;

					if ($_POST->validate()){

						if($activation = ORM::factory('user_activation')->find(array('activation_key' => @$_POST['activation_key']))
							and $activation->id != 0
							and $this->obj = $activation->user
							and $this->obj->id != 0
							):

							$this->obj->setActivated();

							if(Auth::instance()->getCookie('just_registered') == $this->obj->id):
								if(!$this->isLoggedIn()) Auth::instance()->processUserLogin($this->obj);
								Auth::instance()->deleteCookie('just_registered');
							endif;

							$this->redirect = '/activation_success/';
						else:
							$this->errors->add('Неверный ключ активации. Возможно Вы уже активировали свой аккаунт, попробуйте <a href="/login/" onclick="$(\'#login\').click(); return false;">авторизироваться</a>.<br> По возникшим вопросам <a href="/contacts/">пишите нам</a>.');
						endif;

					}else{
						$this->errors->add($_POST->list_errors());
						$this->obj->user_activation->activation_key = $_POST['activation_key'];
					}

				endif;
			}
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->view = new View('auth/activate_view');
		$this->title = 'Подтверждение регистрации';

		unset($this->obj);
	}

	public function activation_success(){
		$this->view = new View('auth/register_approved_view');
		$this->title = 'Спасибо за подтверждение регистрации!';
	}


	public function lostpass_old(){
		$this->view = new View('auth/lostpass_view');
		$this->title = 'Забыли пароль?';

//*
		try{
//*/
			if (!empty($_POST)){

				$_POST = new Validation($_POST);

				//add rules, filters
				$_POST->pre_filter('trim',true)
						->add_rules('email','required',array('valid','email'))
						->add_callbacks('captcha_code', array($this, 'check_captcha'))
						->post_filter('strtolower', 'email');

				if ($_POST->validate()){

					if($this->obj = $this->model->find(array('email' => $_POST['email'])) and $this->obj->id != 0):

						$user_password = $this->obj->user_password;
						$new_password = $user_password->regenerate();

						$lostpass_data = array(
							'email' => $this->obj->email,
							'new_password' => $new_password
						);

						if (Auth::instance()->sendLostpassEmail($lostpass_data)):

							$user_password->save();
							$this->redirect = '/lostpass_success/';

						endif;

					else:
						$this->errors->add('В нашей базе нет пользователя с указанной электронной почтой.');
					endif;

				}else{

					$this->errors->add($_POST->list_errors());

				}
				unset($this->obj);
			}
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);

		}
//*/
	}

	public function lostpass(){
		// die('sfsdf');
		$this->view = new View('auth/lostpass_view');
		$this->title = 'Забыли пароль?';

//*
		try{
//*/
            Auth::instance()->deleteCookie('change_password_successfull');

			if (!empty($_POST)){

				$_POST = new Validation($_POST);

				//add rules, filters
				$_POST->pre_filter('trim',true)
						->add_rules('email','required',array('valid','email'))
						->add_callbacks('captcha_code', array($this, 'check_captcha'))
						->post_filter('strtolower', 'email');

				if ($_POST->validate()){

					if($this->obj = $this->model->find(array('email' => $_POST['email'])) and $this->obj->id != 0):

						$user_password_activation = new User_Password_Activation_Model;
						$user_password_activation->{$this->obj->foreign_key()} = $this->obj->id;

						$lostpass_data = array(
							'email' => $this->obj->email,
							'activation_key' => $user_password_activation->activation_key
						);

						if (Auth::instance()->sendLostpassEmail($lostpass_data)):

                            // устанавливаем флаг, означающий что был сделан запрос на
                            // восстановление пароля
                            Auth::instance()->setCookie('change_password_requested', 1, $sticky = false, $expires_x_days = 1);

    						$user_password_activation->save();
//							$this->redirect = '/change_password/';

						endif;

					else:
						$this->errors->add('В нашей базе нет пользователя с указанной электронной почтой.');
					endif;

				}else{

					$this->errors->add($_POST->list_errors());

				}
				unset($this->obj);
			}
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);

		}
//*/
	}

	public function change_password(){
		$this->template->dontDisplayNotActivatedWarning = true;
//*
		try{
            $change_password_requested = Auth::instance()->getCookie('change_password_requested', 0);
            // проверяем был ли запрос на смену пароля,
            // если нет перенаправляем на страницу восстановления пароля
            if (!$change_password_requested){
                url::redirect('/lostpass/');
            }

//*/
			if (!empty($_POST)){

                if(isset($_POST['activation_key'])):

    				$_POST = new Validation($_POST);

					//add rules, filters
					$_POST->pre_filter('trim',true)
                        ->add_rules('activation_key','required')

                        ->add_rules('password','required','length[4,32]')
                        ->add_rules('repeat_password','required','length[4,32]')

                        ->add_callbacks('password', array($this, 'check_password'))

                        ->add_callbacks('captcha_code', array($this, 'check_captcha'))
					;

					if ($_POST->validate()){

						if($activation = ORM::factory('user_password_activation')->find(array('activation_key' => @$_POST['activation_key']))
                                and $activation->id != 0
                                and $this->obj = $activation->user
                                and $this->obj->id != 0
							):

                            $this->obj = $this->model->find($activation->user_id);
                            $user_password = $this->obj->user_password;
                            $user_password->setPassword($_POST['password']);
                            $user_password->save();

                            $activation->delete();

                            Auth::instance()->deleteCookie('change_password_requested');
                            // устанавливаем флаг, означающий что смена пароля прошла успешно
                            Auth::instance()->setCookie('change_password_successfull', 1, $sticky = false, $expires_x_days = 1);

							$this->redirect = 'lostpass_success';
						else:
							$this->errors->add('Неверный ключ активации. Возможно Вы уже изменили пароль к своему аккаунту, попробуйте <a href="/login/" onclick="$(\'#login\').click(); return false;">авторизироваться</a>.<br> По возникшим вопросам <a href="/contacts/">пишите нам</a>.');
						endif;

					}else{
						$this->errors->add($_POST->list_errors());
						$this->obj->user_password_activation->activation_key = $_POST['activation_key'];
					}

				endif;
			}
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		$this->view = new View('auth/change_password');
		$this->title = 'Подтверждение смены пароля';

        if (!empty($_GET)) {
            $this->view->activation_key = $_GET['activation_key'];
        }

		unset($this->obj);
	}

	public function lostpass_success(){

        $change_password_successfull = Auth::instance()->getCookie('change_password_successfull', 0);
        // проверяем была ли смена пароля успешной,
        // если нет перенаправляем на страницу восстановления пароля
        if (!$change_password_successfull){
            url::redirect('/lostpass/');
        }
        Auth::instance()->deleteCookie('change_password_successfull');

        $this->view = new View('auth/lostpass_success_view');
		$this->title = 'Смена пароля!';
	}


	public function login(){
		// die("login funktion");	
		if ($this->isLoggedIn()) {
			$this->redirect = '/';
			return;
		}
		try{
			if (!empty($_POST)){

				$_POST = new Validation($_POST);

				//add rules, filters
				$_POST->pre_filter('trim',true)
					->pre_filter('strtolower', 'email')
					->add_rules('email','required',array('valid','email'))
					->add_rules('password','required');

				if ($_POST->validate()){

					if($this->obj = $this->model->find(array('email' => $_POST['email'])) and $this->obj->id != 0){

						if (!$this->obj->user_password->checkPassword($_POST['password'])) {
							$this->errors->add('Неправильный пароль!');
						}

						if (!$this->obj->isActivated()) {
							$this->errors->add('Вы не активировали свой аккаунт!');
						}

					}else{
						$this->errors->add('Неправильный логин или пароль');
					};

					if($this->errors->is_empty()){
						// осуществляем вход пользователя
						Auth::instance()->processUserLogin($this->obj, @$_POST['remember_me']);

						unset($this->obj);

						$this->redirect = '/';

					}

				}else{

					$this->errors->add($_POST->list_errors());

				}

			}
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);

		}
//*/
		$this->view = new View('auth/login_view');
		$this->title = 'Вход';

		if(!$this->errors->is_empty()){
			$this->obj->email = $_POST['email'];
			$this->view->obj = $this->obj;
			$this->includeindata = array('email');
		}
	}

	public function logout(){
		Auth::instance()->destroy_session(true);
		AppLib::resetUserRegions();
		header('Location: http://11.lo/');
		exit();
	}


	public function edit(){
		$this->login();
	}

/**
 * CHANGE EMAIL CONFIRMATION
 */


	public function email_change(){

//*
		try{
//*/
			if (! empty($_GET) or !empty($_POST)){

				if(!empty($_GET)) $_POST = new Validation($_GET);
				else $_POST = new Validation($_POST);

				//add rules, filters
				$_POST->pre_filter('trim',true)
					->pre_filter('strtolower', 'email')
					->add_rules('email','required',array('valid','email'))
					->add_rules('code','required');

				if ($_POST->validate()){

					$changed_email = ORM::factory('user_changed_email')->find(array('new_email' => $_POST['email']));

					if($changed_email->id != 0 and $changed_email->confirmation_code == $_POST['code']):

						$changed_email->user->email = $changed_email->new_email;
						$changed_email->user->setUpdated();
						$changed_email->user->save();
						$changed_email->delete();

						$this->redirect = '/email_change_success/';

					else:
						$this->errors->add('В нашей базе нет заявок на смену этого E-mail.');
					endif;

				}else{
					$this->errors->add($_POST->list_errors());
				}

			}
//*
		} catch (Kohana_Exception $e) {
					$this->handleException($e);
		}
//*/
		if(empty($this->redirect)):
			$this->view = new View('auth/email_change_view');
			if(!empty($_POST)):
				$this->view->email = $_POST['email'];
				$this->view->code = $_POST['code'];
			endif;
			$this->title = 'Подтверждение смены E-mail';
		endif;
	}

	public function email_change_success(){

//		$this->view = new View('auth/email_change_success_view');

		$this->title = 'Ваш адрес электронной почты успешно изменён!';
	}


}
/* ?> */