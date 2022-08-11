<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Profile controller
 */		
 
				
				
class Settings_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function unique_email(Validation $v){
	
		if(empty($v->email)) return true;

		$id = ORM::factory('user')->select('id')->find(array('email' => $v->email))->id;

		if($id == 0 or (!empty($this->user) and $this->user->id == $id)) return true;		
		
		$v->add_error('email', 'email_exists');
		
		return false;
	}

	public function index(){	
	
		if(!$this->hasAccess('user')) return;		
//*				
		try{

// */				
			if (!empty($_POST)){

				
				$_POST = new Validation($_POST);
				
				$_POST->pre_filter('trim',true)	
/**
* USER DATA 
*/
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
					
/*					->add_rules('password', 'length[4,32]')
					->add_rules('new_password', 'length[4,32]')
					->add_rules('repeat_password','length[4,32]')
					->add_callbacks('password', array($this, 'check_password'))
*/					->post_filter('strtolower', 'email')
					->post_filter('strtolower', 'secondary_email')
					
				;
				
				$isvalid = $_POST->validate();
				
				if($this->user->name !== $_POST->name):
					$this->user->checked = 0;
					$this->user->name = @$_POST->name;
				endif;				
							
				$this->user->name_status = !empty($_POST->name_status)?'disabled':'enabled';		
				
				$this->user->secondary_email = @$_POST->secondary_email;
				$this->user->email_status = !empty($_POST->email_status)?'disabled':'enabled';				
				
				$this->user->region_id = @$_POST->region_id;					
				$this->user->gender = @$_POST->gender;	
				$this->user->phone = @$_POST->phone;				
				
				if($this->user->address !== $_POST->address):
					$this->user->checked = 0;
					$this->user->address = @$_POST->address;
				endif;
				
				if($this->user->reference_point !== $_POST->reference_point):
					$this->user->checked = 0;
					$this->user->reference_point = @$_POST->reference_point;
				endif;

				if($this->user->website !== $_POST->website):
					$this->user->checked = 0;
					$this->user->website = @$_POST->website;
				endif;
				
				$this->user->notifications = !empty($_POST->notifications)?'enabled':'disabled';		
				$this->user->sms_notifications = !empty($_POST->sms_notifications)?'enabled':'disabled';
				
				$this->user->link_to_other_offers = !empty($_POST->link_to_other_offers)?'enabled':'disabled';		

				
				if ($isvalid):				
					
					$this->user->setUpdated();
					$this->user->save();
					$this->messages->add('Ваши данные успешно сохранёны!');

/**
 * Смена пароля

					if(!empty($_POST->new_password)):
						$this->user->user_password->setPassword($_POST['new_password']);
						$this->user->user_password->save();
						$this->messages->add('Пароль успешно изменён!');
					endif;

 */

/**
 * Смена e-mail
 */					
					if($this->user->email != $_POST['email']):
					
//						text::print_r($this->user->user_changed_email);
					
						if($this->user->user_changed_email->id == 0):
							$changed_email = new User_Changed_Email_Model;
						else:
							$this->user->user_changed_email->generate_confirmation_code();
							$changed_email = &$this->user->user_changed_email;
						endif;

						$changed_email->new_email = $_POST['email'];
						$changed_email->{$this->user->foreign_key()} = $this->user->id;
						$changed_email->save();
						
						$message_data['email'] = $_POST['email'];
						$message_data['code'] = $changed_email->confirmation_code;
															
						$message_tpl = 'auth/email/email_change_view';
						$subject = 'Подтвержение смены основного email!';
	
						Lib::sendEmail($subject, $message_tpl, $message_data);
						
						$this->messages->add('ВНИМАНИЕ! На новый адрес E-mail был отправлено письмо для подтверждения!<br>До тех пор пока Вы не подтвердите новый адрес, вход на сайт будет осуществляться по старому.');
						
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
		
		if(!request::is_ajax()):
		
			$this->template->titleBlock = new View('my/b_title_view');
			$this->title = $this->template->titleBlock->title = "Настройки";
			$this->template->titleBlock->pageid = 'settings';
			$this->addJs('profile.js');	
			$this->view = new View('my/settings_view');
			$this->view->obj = $this->user;
			
		endif;		
	}


}
/* ?> */