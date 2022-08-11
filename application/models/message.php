<?php defined('SYSPATH') or die('No direct script access.');

class Message_Model extends ORM {

	protected $belongs_to = array('user', 'offer', 'message');
//	protected $has_many = array('messages');
	
	protected $sorting = array('added' => 'desc');	
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
		
		if(empty($id)):
			$this->added = date::getForDb();
			$this->status = 'new';
		endif;
	}

	public function __get($column){
	
		switch((string) $column):
			case 'url':
				if (empty($this->object[$column])) {
					$this->object[$column] = '/my/message/' . $this->id .'/';
				}
				return $this->object[$column];
			break;
			case 'url_reply':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url . '#reply';
				}
				return $this->object[$column];
			break;
			case 'url_reply_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = "/my/messages/outbox/?success";
				}
				return $this->object[$column];
			break;
			case 'url_delete':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url . 'delete/';
				}
				return $this->object[$column];
			break;
			case 'url_delete_success':
				if (empty($this->object[$column])) {
					$this->object[$column] = $this->url . 'delete_success/';
				}
				return $this->object[$column];
			break;
			
			case 'sender_name':
				if (!isset($this->object[$column])) {

					if(!empty($this->user_id)):
						$this->object[$column] = $this->user->contact_name;
					elseif(!empty($this->name)):
						$this->object[$column] = $this->name;
					else:
						$this->object[$column] = '';
					endif;					
				}
				
				return $this->object[$column];
			break;
			case 'sender_name_html':
				if (!isset($this->object[$column])) {

					if(empty($this->user_id) and !empty($this->email)):
						$this->object[$column] = html::mailto($this->email, $this->name?$this->name:$this->email);
					elseif(!empty($this->user_id)):
						$this->object[$column] = '<a href="'.$this->user->url.'">'.$this->user->own_name.'</a>';
					elseif(!empty($this->name)):
						$this->object[$column] = $this->name;
					else:
						$this->object[$column] = '';
					endif;					
				}
				
				return $this->object[$column];
			break;
			case 'sender_email':
				if (!isset($this->object[$column])) {				
					if(empty($this->user_id) and !empty($this->email)):
						$this->object[$column] = $this->email;
					elseif(!empty($this->user_id)):
						$this->object[$column] = $this->user->contact_email;
					else:
						$this->object[$column] = '';
					endif;					
				}
				return $this->object[$column];
			break;
			case 'sender_email_html':
				if (!isset($this->object[$column])) {				
					if(!empty($this->sender_email)):
						$this->object[$column] = html::mailto($this->sender_email);
					else:
						$this->object[$column] = '';
					endif;					
				}
				return $this->object[$column];
			break;
			case 'sender_phone':
				if (!isset($this->object[$column])) {				
					if(empty($this->user_id) and !empty($this->phone)):
						$this->object[$column] = format::phone($this->phone);
					elseif(!empty($this->user_id)):
						$this->object[$column] = $this->user->public_phone;
					else:
						$this->object[$column] = '';
					endif;					
				}
				return $this->object[$column];
			break;
/* statuses */
			case 'is_viewed_by_owner':
				if (!isset($this->object[$column])) {
					if($user = Auth::instance()->user):	
						$this->object[$column] = ($user->id == $this->user_id or $user->id == $this->to_user);
					else:
						$this->object[$column] = false;
					endif;
				}
				return $this->object[$column];
			break;
			case 'is_repliable':
				if (!isset($this->object[$column])) {
					$this->object[$column] = (!empty($this->user_id) or $this->sender_email);
				}
				return $this->object[$column];
			break;
			
			
					
			case 'short_content':
				if (!isset($this->object[$column])) {
					if(!empty($this->content)):
						$this->object[$column] = text::limit_chars(text::untypography($this->content), 100, '...', TRUE);
					else:
						$this->object[$column] = '';
					endif;					
				}
				return $this->object[$column];
			break;
			
			case 'short_reply_to_content':
				if (!isset($this->object[$column])) {
					if(!empty($this->content)):
						$this->object[$column] = text::limit_chars(text::untypography($this->content), 120, '...', TRUE);
					else:
						$this->object[$column] = '';
					endif;					
				}
				return $this->object[$column];
			break;
		endswitch;
	
		return parent::__get($column);
	
	}

	public function countIncoming($user_id, $status = NULL){
	
		if(!empty($status))	$this->where('status', $status);

		
		return $this->where(array('to_user' => $user_id, 'deleted_by !=' => $user_id))->count_all();
	}

	public function countOutgoing($user_id){
	
		if(!empty($status))	$this->where('status', $status);

	
		return $this->where(array('user_id' => $user_id, 'deleted_by !=' => $user_id))->count_all();
	}
	
	public function findIncoming($user_id, $limit = NULL, $offset = NULL){
	
		return $this->where(array('to_user' => $user_id, 'deleted_by !=' => $user_id))->find_all($limit, $offset);
	}
	
	public function findOutgoing($user_id, $limit = NULL, $offset = NULL){
	
		return $this->where(array('user_id' => $user_id, 'deleted_by !=' => $user_id))->find_all($limit, $offset);
	}

	public function setRead($id = NULL){
		if(!empty($id)):
			if(is_array($id)):
				return $this->db->from($this->table_name)->set('status' , 'read')->in($this->primary_key, $id)->update();
			else:
				return $this->db->from($this->table_name)->set('status' , 'read')->where($this->primary_key, $id)->update();
			endif;
		endif;
		if(!empty($this->id)):
			$this->status = 'read';
			return $this->save();
		endif;		
		
		return false;
	}

	public function delete($id = NULL, $user_id = NULL){
	
		if(empty($user_id)):
			$user_id = @$this->user_id;			
		endif;
	
		if(!empty($id)):
		
			if(is_array($id)):

				$list = $this->in($this->primary_key, $id)->find_all();
				
				foreach($list as $item):
					$item->delete(NULL, $user_id);
				endforeach;
				
				return true;
				
			else:
			
				return $this->where($this->primary_key, $id)->find()->delete(NULL, $user_id);
				
			endif;
			
		elseif (empty($this->id)):
		
			return FALSE;
		
		endif;
	
		if(empty($this->user_id) or $this->deleted_by == $user_id or $this->user_id == $this->to_user):
			parent::delete();
		else:
			$this->deleted_by = $user_id;
			$this->save();
		endif;
		
	}

	public function countForOffer($id, $status = NULL){
	
		if(!is_array($id)) $id = array($id);
		$this->db->select('count(*) as amount, offer_id')
			->from($this->table_name)
			->groupby('offer_id')
			->in('offer_id', $id)
			->where('reply_to', '')
		;
		
		if(!empty($status)) $this->db->where('status', $status);
		
		$counts = $this->db->get();
		
		$array = array();
		
		foreach($counts as $item):
			$array[$item->offer_id] = $item->amount;
		endforeach;
		
		return $array;
		
	}

}
/* ?> */
