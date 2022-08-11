<?php defined('SYSPATH') or die('No direct script access.');

class valid extends valid_Core{

	/**
	 * Checks if a phone number is valid.
	 *
	 * @param   string   phone number to check
	 * @return  boolean
	 */
	public static function phone($number, $lengths = NULL)
	{	
		$number = preg_replace('/\s\t/uis', ' ', $number);
		return preg_match('/^(\+?\s?(\(?\s*\+?\s*\d{3}\s*\)?[-\s\.]?)?(\(?\d{2,3}\)?[-\s\.]?)?\d{2,3}[-\s\.]?\d{2}[-\s\.]?\d{2}[,\s]?){1,}$/ius', $number);
	}

	public static function numeric($str)
	{
		// Use localeconv to set the decimal_point value: Usually a comma or period.
		$locale = localeconv();
		return (preg_match('/^[-0-9'.$locale['decimal_point'].'\.]++$/D', (string) $str));
	}

	
	public static function year($str){
		return preg_match('/^\d{4}$/', (string) $str);
	}

	public static function datetime($str){
		return true;
	}

	public static function date($str){
		return true;
	}
	
	public static function time($str){
		return true;
	}	


/**
 * Check whether image is valid
 * returns Error message if failed or false in case image is correct
 */

	public function image($mode = 'default', $imageArray = NULL, $key = NULL, $imagetitle = NULL){
	
		if($imageArray == NULL) $imageArray = $_FILES['image'];
	
		if($key !== NULL):
			$image['name'] = $imageArray['name'][$key];
			$image['error'] = $imageArray['error'][$key];
			$image['type'] = $imageArray['type'][$key];
			$image['tmp_name'] = $imageArray['tmp_name'][$key];
			$image['size'] = $imageArray['size'][$key];
		else:
			$image = $imageArray;
		endif;
		
		if(empty($imagetitle)) $imagetitle = '&laquo;Изображение'.($key != NULL?' №'.($key+1):'').'&raquo;';		
		
		if(empty($image['error'])):
			if(!in_array($image['type'], Lib::config('picture.allowed_types'))):
				$errorText = 'Неверный формат '.$imagetitle.'. Допустимы только: ' . Lib::config('picture.allowed_types_string').'.';
			else:
			
				list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);

				if($width < Lib::config('picture.'.$mode, 'width_min') || $height < Lib::config('picture.'.$mode, 'height_min')):
					$errorText = $imagetitle.' слишком мало. Должно быть не менее: ' . Lib::config('picture.'.$mode, 'width_min') .'x'. Lib::config('picture.'.$mode, 'height_min').'.';
				endif;

			endif;
		else:
			if(empty($this->file_errors)) $this->file_errors = Lib::config('picture.file_errors');
			
			if(Lib::config('picture.file_errors', $image['error'])):
				$errorText = sprintf(Lib::config('picture.file_errors', $image['error']), $imagetitle);
			endif;
		endif;
			
		if(!empty($errorText)) return $errorText;
		
		return false;
		
	}	



		
}

/* ?> */