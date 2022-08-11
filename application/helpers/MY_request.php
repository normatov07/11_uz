<?php defined('SYSPATH') or die('No direct script access.');

class request extends request_Core{

	/**
	 * Tests if the current request is an AJAX request by checking the X-Requested-With HTTP
	 * request header that most popular JS frameworks now set for AJAX calls.
	 *
	 * @return  boolean
	 */
	public static function is_ajax()
	{
		
		if($res = parent::is_ajax()):
			return $res;
		else:
			if(!empty($_POST) and !empty($_POST['is_ajax'])):
				if(!empty($_FILES) and count($_FILES)):
					return 2;
				else:
					return 1;
				endif;
			else:
				return false;
			endif;
		endif;
				
	}
		
}

/* ?> */