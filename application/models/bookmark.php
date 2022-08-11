<?php defined('SYSPATH') or die('No direct script access.');

class Bookmark_Model extends ORM {

	protected $belongs_to = array('user', 'offer');
	
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
			case 'url':
				if (empty($this->object[$column])) {
					$this->object[$column] = '/my/bookmark/' . $this->id .'/';
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
			case 'is_viewed_by_owner':
				if (!isset($this->object[$column])) {
					if($user = Auth::instance()->user):	
						$this->object[$column] = ($user->id == $this->user_id);
					else:
						$this->object[$column] = false;
					endif;
				}
				return $this->object[$column];
			break;
		endswitch;

		return parent::__get($column);
	
	}
	
	public function count_all_by_user($user_id){
	
		return $this->where(array('user_id' => $user_id))->count_all();
		
	}
	
	public function find_all_by_user($user_id, $limit = NULL, $offset = NULL){
	
		return $this->where(array('user_id' => $user_id))->find_all($limit, $offset);
		
	}
	
}
/* ?> */