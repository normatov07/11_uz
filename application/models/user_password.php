<?php defined('SYSPATH') or die('No direct script access.');

class User_Password_Model extends ORM {

	protected $belongs_to = array('user');
	
	public $password;
	
	public function __construct($id=NULL)
	{
		parent::__construct($id);
	}

	public function setPassword($password) {
		$this->salt = $this->generate_pass_salt();
		$this->hash = $this->generate_compiled_passhash($this->salt, $password);
	}
	
	public function checkPassword($password) {
		return ($this->getHash() === $this->generate_compiled_passhash($this->salt, $password));
	}
	
	public function regenerate() {
		$new_password = $this->generate_pass_salt(Lib::config('auth.generated_password_length'));
		$this->setPassword($new_password);
		return $new_password;
	}
	
	public function getHash() {
		return $this->hash;
	}
	
	private function generate_compiled_passhash($salt, $pass) {
		return sha1(sha1($salt) . sha1($pass));
	}

	private function generate_pass_salt($len = 5) {		
		return text::random('alnum', $len);
	}



}
/* ?> */