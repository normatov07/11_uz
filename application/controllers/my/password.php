<?php defined('SYSPATH') or die('No direct script access.');
				
class Password_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function check_password(Validation $v){
	
		if(!$this->hasAccess('user')) return;
		
		if(!empty($v->new_password)):
			if(empty($v->password)):
				$v->add_error('password', 'current_password_is_required');
				return false;
			elseif(!$this->user->user_password->checkPassword($_POST['password'])):
				$v->add_error('password', 'incorrect_current_password');
				return false;
			else:
				if(empty($v->new_password) or $v->new_password != $v->repeat_password):
					$v->add_error('new_password', 'new_password_is_required');
					return false;
				endif;
			endif;
		endif;	
		
		return true;
		
	}



	public function index()
	{
	
		if(!$this->hasAccess('user')) return;
		
		
		
//*				
		try{

// */				
			if (!empty($_POST)){

				
				$_POST = new Validation($_POST);
				
				$_POST
/**
* USER DATA 
*/
					->add_rules('password', 'required', 'length[4,32]')
					->add_rules('new_password', 'required', 'length[4,32]')
					->add_rules('repeat_password', 'required', 'length[4,32]')
					->add_callbacks('password', array($this, 'check_password'))
				;
				
				$isvalid = $_POST->validate();
			
				if ($isvalid):				
					
/**
 * Смена пароля
 */
					if(!empty($_POST->new_password)):
						$this->user->user_password->setPassword($_POST['new_password']);
						$this->user->user_password->save();
						$this->messages->add('Пароль успешно изменён!');
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
			$this->title = $this->template->titleBlock->title = "Изменить пароль";
			$this->template->titleBlock->pageid = 'password';
			$this->addJs('profile.js');	
			$this->view = new View('my/password_view');
			$this->view->obj = $this->user;
			
		endif;		
	}


}
/* ?> */