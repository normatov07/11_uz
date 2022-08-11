<?php defined('SYSPATH') or die('No direct script access.');

class User_Session_Model extends ORM {

	protected $belongs_to = array('user');
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
		
//		$this->user_agent = new User_agent;
		
		$this->session_id = sha1(uniqid(microtime()));
		$this->running_time = time();
		$this->ip_address = $this->_ip_address();
		$this->user_agent = $this->_user_agent();
		
	}

	private static function _ip_address() {		
		return Input::instance()->ip_address();
	}	
	
	/**
	 * Get User Agent
	 *
	 * @return string
	 */
	private static function _user_agent() {
		return substr(Kohana::user_agent(), 0, 128);
	}	

	public function findBySessionId($session_id) {
	
		$this->where('session_id', $session_id);
		$this->join('users', array('users.id'=>'user_id'));
		
		if (Lib::config('auth.us_match_user_agent')) {
			$this->db->where('user_agent', $this->_user_agent());
		}
		
		if (Lib::config('auth.us_match_ip')) {
			$this->db->where('ip_address', $this->_ip_address());
		}
						
		return $this->find();
	}


	public function deleteExpired($expiration_time = FALSE){
		if($expiration_time):
			$this->db->from($this->table_name)->where('running_time <', (int) $expiration_time)->delete();
		endif;
	}
}
/* ?> */