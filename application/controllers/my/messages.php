<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Messages controller
 */
class Messages_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->perpage = 20;
	}

	public function index($mode = 'inbox', $page = 0)
	{

		if(!$this->hasAccess('user')) return;
		
		if(!in_array($mode, array('inbox','outbox'))) $mode = 'inbox';
	
		
		if(!empty($_POST)):
			if(!empty($_POST['i']) and count($_POST['i'])):
		
				if(!empty($_POST['delete_selected'])):
	
					if(ORM::factory('message')->delete($_POST['i'], $this->user->id)):
				
						$this->data['act'] = 'delete_selected';
						$this->messages->add('Сообщения удалены!');
						
					endif;
					
				elseif(!empty($_POST['setread_selected'])):
					if(ORM::factory('message')->setRead($_POST['i'])):
						$this->data['act'] = 'setread_selected';
						$this->messages->add('Сообщения отмечены как прочитанные!');
					endif;
				endif;
			
			else:
				$this->errors->add('Не выбрано ни одного сообщения!');
			endif;
		endif;
		
		
		if(!request::is_ajax()):
		
			$this->template->titleBlock = new View('my/b_title_view');
			$this->title = $this->template->titleBlock->title = "Мои сообщения";
			$this->template->titleBlock->pageid = 'messages';
			$this->view = new View('my/messages_view');

			$this->addJs('edit_list.js');	
			
			$this->view->mode = $mode;				
			
			$this->view->count_incoming = ORM::factory('message')->countIncoming($this->user->id);
			$this->view->count_outgoing = ORM::factory('message')->countOutgoing($this->user->id);
			
			switch($mode):
				case 'inbox':
					$totalCount = $this->view->count_incoming;
				break;
				case 'outbox':
					$totalCount = $this->view->count_outgoing;
				break;
			endswitch;
						
			$paginationConfig = array(
				'total_items'    => $totalCount, // use db count query here of course
				'items_per_page' => $this->perpage, // it may be handy to set defaults for stuff like this in config/pagination.php
				'item_title'		=> array('сообщение','сообщения', 'сообщений'),
			);
			
			$pagination = new Pagination($paginationConfig);
		
			switch($mode):
				case 'inbox':
					$this->view->messageList = ORM::factory('message')->findIncoming($this->user->id, $this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
				break;
				case 'outbox':
					$this->view->messageList = ORM::factory('message')->findOutgoing($this->user->id, $this->perpage, ($pagination->current_page > 0 ? $pagination->current_page - 1:0) * $this->perpage);
				break;
			endswitch;		
						
			$this->view->offerIDs = $this->view->messageList->getValues('offer_id');
			if(count($this->view->offerIDs)) $this->view->offerListByID = ORM::factory('offer')->in('id', $this->view->offerIDs)->find_all()->as_id_array();

/*			$this->view->replyToIDs = $this->view->messageList->getValues('reply_to');
			$this->view->replyToListByID = ORM::factory('message')->in('id', $this->view->replyToIDs)->find_all()->as_id_array();
*/			

			if($mode == 'inbox'):
				$this->view->userIDs = $this->view->messageList->getValues('user_id');
			else:
				$this->view->userIDs = $this->view->messageList->getValues('to_user');
			endif;
			
			if(count($this->view->userIDs)) $this->view->userListByID = ORM::factory('user')->in('id', $this->view->userIDs)->find_all()->as_id_array();

			$this->view->pagination = $pagination;
			
			if($mode == 'outbox' and isset($_GET['success'])) $this->messages->add('Сообщение успешно отправлено!');
			
		endif;
		
		
	}



/**
 * REPLY
 */


	public function reply($message_id = NULL){
	
		if(!$this->hasAccess('user')) return;
		
		if(empty($message_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;
		
		$this->message = new message_Model($message_id);
		
		if($this->message->id == 0 or !$this->message->is_viewed_by_owner) { $this->redirect = '/'; return; }
		
		$this->message->status = 'read';
		$this->message->save();
//*
		try{
//*/		
			if (! empty($_POST)):
			
				if(!empty($_POST['cancel'])):
					$this->redirect = '/my/messages/';
					return;
				else:

					$_POST = new Validation($_POST);
									
					$_POST->pre_filter('trim',true)		
						->pre_filter('strip_tags',true)			
						->pre_filter('text::break_long_words',true)
						
						->add_rules('content', 'required', 'length[2,2048]')
						;
						
					if ($_POST->validate()):

						$reply = new Message_Model;
						$reply->name = $this->message->name;
						$reply->email = $this->message->email;
						$reply->{$this->user->foreign_key()} = $this->user->id;
						$reply->to_user = @$this->message->user_id;
						$reply->{$this->message->offer->foreign_key()} = $this->message->offer->id;
						$reply->content = text::typography($_POST->content);
						$reply->reply_to_content = $this->message->content;
						$reply->reply_to = $this->message->id;
						
						$reply->save();

						if($this->message->is_repliable):
							
							$message_data = array(
								'email' => $this->message->sender_email,
								'offer_title' => text::untypography($this->message->offer->fulltitle),
								'reply_to_content' => text::untypography($reply->reply_to_content),
								'message_content' => text::untypography($reply->content),
								
								'author_name' => $reply->sender_name,
								'author_email' => $reply->sender_email,
								'author_phone' => format::phone($reply->sender_phone),
							);
							
							if($this->message->is_repliable):
								$message_data['message_url'] = @$reply->url;
								$message_data['message_url_reply'] = @$reply->url_reply;
							endif;
										
							$message_tpl = 'offer/email/message_reply_view';
							$subject = 'Новый ответ на Ваше сообщение!';
		
							Lib::sendEmail($subject, $message_tpl, $message_data);	
									
						endif;		

						$this->messages->add('Ваше ответ отправлен!');
						
						$this->redirect = $this->message->url_reply_success;
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
		
		if(!request::is_ajax()):
		
			$this->template->titleBlock = new View('my/b_title_view');
			$this->title = $this->template->titleBlock->title = "Мои сообщения";
			$this->template->titleBlock->pageid = 'messages';
			
			$this->view = new View('my/message_reply_view');

			$this->addJs('message.js');	

			$this->view->count_incoming = ORM::factory('message')->countIncoming($this->user->id);
			$this->view->count_outgoing = ORM::factory('message')->countOutgoing($this->user->id);
	
			$this->view->messageList = ORM::factory('message')->where(array('offer_id' => $this->message->offer_id, 'id!='=>$this->message->id))->findIncoming($this->user->id);

			$this->view->message = $this->message;		
	//		if(empty($_POST)) $this->returnViewInAjax = true;
	
		endif;
		
	} // delete

	public function reply_success(){
		
		$this->title = 'Ваш ответ отправлен!';
		
	}






/**
 * Удаление пользователем
 */


	public function delete($message_id = NULL){

		if(!$this->hasAccess('user')) return;

		if(empty($message_id)):
			$this->errors->add('ID is missing.');
			return;
		endif;
		
		$this->message = new message_Model($message_id);
		
		if($this->message->id == 0 or !$this->message->is_viewed_by_owner) { $this->redirect = '/'; return; }
		
//*
		try{
//*/		
			if (! empty($_POST)):
			
				if(!empty($_POST['cancel'])):
					$this->redirect = $this->message->url;
					return;
				else:

					$_POST = new Validation($_POST);
									
					$_POST->pre_filter('trim',true);						
				
					if ($_POST->validate()):
						$this->data['id'] = $this->message->id;
						if($this->message->delete()):
							$this->redirect = $this->message->url_delete_success;
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
		
		$this->view = new View('my/message_delete_view');

		$this->title = 'Удаление сообщения';
		$this->template->titleInView = true;		
		
		$this->view->message = $this->message;		
		if(empty($_POST)) $this->returnViewInAjax = true;
		
	} // delete

	public function delete_success($offer_id = NULL){
		
		$this->title = 'Сообщение удалено!';
		
	}


}
/* ?> */