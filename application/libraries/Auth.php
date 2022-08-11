<?php



class Auth_Core {



	static $instance;



	private $now = 0;

	private $session = NULL;

	public $user = NULL;

	private $cookies = array();

	private $session_expiration  = 7200; // в секундах, длина сессии

	private $user_key_expire     = 0;

	private $session_cookie	     = 'session_id';

    private $gc_chance           = 10; // percents



	/**

	 * Return a static instance of Auth.

	 *

	 * @return  object

	 */

	public static function instance()

	{

		// Load the Auth instance

		if(empty(self::$instance)) self::$instance = new Auth();



		return self::$instance;

	}



    public $lib_log;

	function __construct() {



		$this->now = time();

		$this->lib_log = new Lib();

		// 2 hours is default

		$this->session_expiration = $this->conf('us_expiration')?$this->conf('us_expiration')*3600:7200;



		$this->user_key_expire = $this->conf('us_user_key_expire')?$this->conf('us_user_key_expire'):0;



		// Set the session cookie name

		if ($this->conf('us_cookie_name')) {

			$this->session_cookie = $this->conf('us_cookie_name');

		}



		$this->gc_chance = $this->conf('us_gc_chance');



		// Delete old sessions

		$this->garbage_collection();



	}



	function save_models() {



		if ($this->session_loaded() && ($this->session->isNew() || $this->session->isDirty())) {

			$this->session->save();

			$this->log('session saved!');

			$this->set_cookie($this->session_cookie, $this->session->session_id, -1);

			$this->log('cookies sent!');

		}

	}



	/**

	 * Destroy user's cookies

	 *

	 */

	function destroy_cookies($force_sending_cookies = false) {

		$this->set_cookie("user_id", "0", -1);

		$this->set_cookie("pass_hash", "0", -1);

		$this->set_cookie($this->session_cookie, "", -1);



		if ($force_sending_cookies) {

			$this->send_cookies();

		}

	}



	function get_cookie($name) {

		if (!isset($this->cookies[$name])) {

			$this->cookies[$name]['value'] = cookie::get($name, Lib::config('cookie.prefix'));

		}



		return $this->cookies[$name]['value'];

	}



	function set_cookie($name, $value, $sticky = true, $expires_x_days = 0) {

		$this->cookies[$name] = array(

			'value' => $value,

			'sticky' => $sticky,

			'expires_x_days' => $expires_x_days

		);

	}



	function getCookie($name, $default = "") {

		return cookie::get($name, Lib::config('cookie.prefix'));

    }



	function setCookie($name, $value, $sticky = true, $expires_x_days = 0) {



		$prefix = Lib::config('cookie.prefix');

		$domain = Lib::config('cookie.domain');

		$path   = Lib::config('cookie.path');



		if ($sticky === true) {

			$expire = (60*60*24*365); // 1 year

		} elseif($expires_x_days) {

			$expire = ($expires_x_days * 86400);

		} else {

			$expire = 0; // until the browser will be closed

		}



		cookie::set($name, $value, $expire, $path, $domain, NULL, true, $prefix);

	}



	function deleteCookie($name) {



		$domain = Lib::config('cookie.domain');

		$path   = Lib::config('cookie.path');



		return cookie::delete($name, $path, $domain);

	}

	/**

     * Check stronghold cookie (security)

     *

     * @return bool

     */

    function stronghold_check_cookie($user_id, $user_key) {



        $ip_octets  = explode(".", $this->ip_address());



        $crypt_salt = $this->conf('us_encryption_key');



        $cookie = $this->getCookie('stronghold');



        //-----------------------------------------

        // Check

        //-----------------------------------------



        if (!$cookie) {

            return FALSE;

        }



        //-----------------------------------------

        // Put it together....

        //-----------------------------------------



        $stronghold = md5(md5($user_id . "-" . $ip_octets[0] . '-' . $ip_octets[1] . '-' . $user_key) . $crypt_salt);



        //-----------------------------------------

        // Check against cookie

        //-----------------------------------------



        return $cookie === $stronghold;

    }



	function stronghold_set_cookie($user_id, $user_key) {



        $ip_octets  = explode(".", $this->ip_address() );



        $crypt_salt = $this->conf('us_encryption_key');



        //-----------------------------------------

        // Put it together....

        //-----------------------------------------



        $stronghold = md5(md5($user_id . "-" . $ip_octets[0] . '-' . $ip_octets[1] . '-' . $user_key) . $crypt_salt);



        //-----------------------------------------

        // Set the cookie with sticky option (1 year)

        //-----------------------------------------



        $this->setCookie('stronghold', $stronghold, true);

    }



	/**

	 * IP address

	 *

	 * @access	public

	 * @return	string

	 */

	function ip_address() {



		return Input::instance()->ip_address();

	}



	/**

	 * Fetch/set config value

	 *

	 * @param mixed   $key    - array for setting values, string for getting

	 * @return mixed

	 */

	function conf($key = '') {

		return Lib::config('auth.'.$key);

	}



	function log($message) {
		
		if($this->lib_log->config('auth.log_enabled')) return $this->lib_log->log('AUTH: '. $message, 'info');

		else return true;

	}

	/**

	 * Final sending cookies

	 *

	 */

	function send_cookies() {

		foreach($this->cookies as $cookie_name => $cookie) {

			if (!isset($cookie['sticky'])) {

				$cookie['sticky'] = true;

			}

			if (!isset($cookie['expires_x_days'])) {

				$cookie['expires_x_days'] = 0;

			}



			$this->setCookie($cookie_name, $cookie['value'], $cookie['sticky'], $cookie['expires_x_days']);

		}

	}



	/**

	 * Authorize user

	 *

	 */

	function authorize() {



		// First, fetch the cookies and trying to load the session

		$session_id = $this->get_cookie($this->session_cookie);

		$user_id = $this->get_cookie('user_id');

		$pass_hash = $this->get_cookie('pass_hash');



		$this->load_session($session_id); // do we have the session?







		if ($this->session_loaded()) {



			$this->log("session found, updating it...");

			$this->update_session();



		} elseif ($user_id && $pass_hash) {



			//-----------------------------------------------------

			// There is no session or it was incorrect,

			// so try to do smth with cookies

			//-----------------------------------------------------



			$this->log("session doesn't exist, but we have user_id: $user_id and pass_hash: $pass_hash");



			//---------------------------------------------------

			// user key stuff

			//---------------------------------------------------



			if ($this->user_key_expire) {

				$_time   = $this->now + $this->user_key_expire * 86400;

			} else { // unexpired key

				$_time   = 0;

			}



			// load user

			$this->load_user($user_id);







			// is pass_hash ok?

			if ($this->user_loaded() && ($this->user->user_key === $pass_hash)) {

				$this->log("user_id and pass_hash are ok.");

				//---------------------------------------------------

				// Stronghold check

				//---------------------------------------------------

				if ($this->stronghold_check_cookie($this->user->id, $this->user->user_key)) {

					$this->log("stronghold is ok.");

					//---------------------------------------------------

					// User key expired?

					//---------------------------------------------------

					if ($this->user_key_expire) {

						if ($this->now > $this->user->user_key_expire) { // key expired

							$this->log("user key is expired, so destroy the cookies and exit...");

							$this->destroy_cookies();

						} else {

							$this->log("user key didn't expire, create session...");

							$this->create_session();

						}

					} else {

						$this->log("user key can't expire, create session...");

						$this->create_session();

					}

				} else {

					$this->log("stronghold check failed.");

					//---------------------------------------------------

					// Create new user_key as stronghold check failed

					//---------------------------------------------------



					$this->user->generate_user_key();

					$this->user->user_key_expire = $_time;



					$this->destroy_cookies();

				}

			} else { // pass_hash did not match

				$this->user = NULL;

				$this->log("pass_hash did not match");

				$this->destroy_cookies();

			}

		} else { // no user_id and pass_hash

			$this->user = NULL;

			$this->log("no user_id and pass_hash were in cookies");

			$this->destroy_cookies();

		}



		//---------------------------------------------------

		// Update user's last activity

		//---------------------------------------------------



		$this->update_user_last_activity();



		$this->save_models();



		$this->send_cookies();



		if($this->user_loaded() and !empty($this->user)):

			return $this->user;

		endif;



		return NULL;



    }



	function getSession() {

    	return $this->session_loaded() ? $this->session : NULL;

    }



	/**

	 * Create new user session

	 *

	 */

	function create_session() {



		if (!$this->user_loaded()) {

			return;

		}



		// create new session and bind it with the user

		$this->session = new User_Session_Model();

		$this->log("New session created!");

		$this->session->{$this->user->foreign_key()} = $this->user->id;

	}



	function update_session() {



		if (!$this->session_loaded()) {

			return;

		}



		if (($this->now - $this->session->running_time) > $this->session_expiration) {

			// session expired, create new

			$this->log('trying to create new session...');

			$this->create_session();

			return;

		}



		// update session running time

		if ($this->now - $this->session->running_time > 300) {

			$this->session->running_time = $this->now;

		}

	}



	/**

	 * Load session from database by provided session_id

	 *

	 * @param string $session_id

	 * @return void

	 */

	function load_session($session_id = '') {



		if (!$session_id) {

			return;

		}



		$this->session = ORM::factory('user_session')->findBySessionId($session_id);



		if (!$this->session->isNew()){



			$this->load_user();



			if ($this->user_loaded() && !$this->session->{$this->user->foreign_key()}) {

				$this->session->{$this->user->foreign_key()} = $this->user->id;

			}



			$this->set_cookie($this->session_cookie, $this->session->session_id, -1);



		}else{



			$this->session->delete();

			$this->session = NULL;



		}

	}



	function user_loaded() {

		return is_object($this->user);

	}



	function session_loaded() {

		return is_object($this->session);

	}



	function load_user($user_id = 0) {



		if ($this->user_loaded()) {



			return;



		} elseif ($user_id instanceof User_Model) { // пытаемся получить пользователя как объект



			$this->user = $user_id;



		} elseif ($this->session_loaded()) {

			// если пользователя нет, то пытаемся взять его у сессии

			$user_id = $this->session->user_id;

		}



		if (!$this->user_loaded() and $user_id != 0) {

			$this->user = ORM::factory('user', (int) $user_id);

		}



		if ($this->session_loaded() && !$this->session->user_id && $this->user_loaded()) {

			$this->log('user is set to session');

			$this->session->{$this->user->foreign_key()} = $this->user->id;

		}

	}



	function update_user_last_activity() {

		if (!$this->session_loaded()) {

			return;

		}



		if ($this->session->isChanged('running_time') || $this->session->isNew()) {



			if (!$this->user_loaded()) $this->load_user();



			if ($this->user_loaded()) {

				$this->user->last_activity = $this->now;

				$this->user->last_ip = $_SERVER['REMOTE_ADDR'];

				$this->user->save();

			}

		}

	}



	function update_user_key() {



		if (!$this->user_loaded()) {

			return;

		}



		if ($this->user_key_expire && ($this->now > $this->user->user_key_expire)) {

			$_time = $this->now + $this->user_key_expire * 86400;

			$this->user->generate_user_key();

			$this->user->user_key_expire = $_time;

			$this->user->save();



			$this->set_cookie("pass_hash", $this->user->user_key, false, $this->user_key_expire);

		}

	}



	function destroy_session($force_sending_cookies = false) {



		if ($this->session_loaded() && !$this->session->isNew()) {

			$this->session->delete();

		}



		$this->session = NULL;



		$this->destroy_cookies($force_sending_cookies);

	}



	/**

	 * Remove old sessions from database

	 *

	 */

	function garbage_collection() {

		$max_chance = 100; // max probability is 100%



		if ((rand(0, $max_chance)) <= $this->gc_chance) {

			$expired = $this->now - $this->session_expiration;

			$this->log('Garbage collection: session cleaning');

			$session = ORM::factory('user_session')->deleteExpired($expired);

		}

	}



	function create_session_for_user($user) {



		$this->load_user($user);



		$this->create_session();



		$this->update_user_key();



		$this->save_models();



		$this->send_cookies();

	}



	/**

     * Осуществляет вход пользователя (заполнение сессии данными пользователя)

     * @param  object  $user

     * @param  boolean $remember_me

     * @return void

     */

    function processUserLogin($user, $remember_me = false) {

        if (!is_object($user)) {

            return;

        }



        $this->create_session_for_user($user);



        if ($remember_me) {



            // stronghold

            $this->stronghold_set_cookie($user->id, $user->user_key);



            $user_key_expire = $this->conf('us_user_key_expire');



            if ($user_key_expire) {

                $_sticky = false;

                $_days   = $user_key_expire;

            } else { // unexpired key

                $_sticky = true;

                $_days   = 365;

            }



            $this->setCookie('user_id', $user->id);

            $this->setCookie('pass_hash', $user->user_key, $_sticky, $_days);

        }

    }



	/**

	 * Отправка письма от авторизационной системы пользователю

	 *

	 * @param string $subject  - тема письма

	 * @param string $tpl_name - имя шаблона

	 * @param array $tpl_data  - массив с данными для шаблона

	 */



	public function sendLostpassEmail($lostpass_data) {



		$lostpass_tpl = Lib::config('auth.lostpass_email_tpl');

		$subject = Lib::config('auth.lostpass_email_subject');



		return Lib::sendEmail($subject, $lostpass_tpl, $lostpass_data);



	}



	public function sendActivationEmail($activation_data) {



		$activation_tpl = Lib::config('auth.activation_email_tpl');

		$subject = Lib::config('auth.activation_email_subject');



		return Lib::sendEmail($subject, $activation_tpl, $activation_data);

	}



}



