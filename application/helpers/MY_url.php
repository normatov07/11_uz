<?php defined('SYSPATH') or die('No direct script access.');

class url extends url_Core{

	public static $tr = array(
	   "Ґ"=>"G","Ё"=>"Yo","Є"=>"E","Ї"=>"Yi","І"=>"I",
	   "і"=>"i","ґ"=>"g","ё"=>"yo","№"=>"#","є"=>"e",
	   "ї"=>"yi","А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
	   "Д"=>"D","Е"=>"E","Ж"=>"Zh","З"=>"Z","И"=>"I",
	   "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
	   "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
	   "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"Ts","Ч"=>"Ch",
	   "Ш"=>"Sh","Щ"=>"Sch","Ъ"=>"'","Ы"=>"Yi","Ь"=>"",
	   "Э"=>"E","Ю"=>"Yu","Я"=>"YA","а"=>"a","б"=>"b",
	   "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",
	   "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
	   "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
	   "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
	   "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"'",
	   "ы"=>"i","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
	);

	public static function base($index = FALSE, $protocol = FALSE)
	{
		if(Lib::config('app.disable_baseurl')) return '/';
		else parent::base($index, $protocol);
	}
	
	public static function title($title, $separator = '-') {
		$title = strtr($title, self::$tr);
		return parent::title($title,$separator);
	}
		
}

/* ?> */