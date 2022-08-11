<?php defined('SYSPATH') or die('No direct script access.');

class num extends num_Core{
	
	static $Expon = array();

	static function DescrIdx($ins){
		if(intval($ins/10) == 1) // числа 10 - 19: 10 миллионов, 17 миллионов
			return 2;
		else
			{
			// для остальных десятков возьмем единицу
			$tmp = $ins%10;
			if($tmp == 1) // 1: 21 миллион, 1 миллион
				return 0;
			else if($tmp >= 2 && $tmp <= 4)
				return 1; // 2-4: 62 миллиона
			else
				return 2; // 5-9 48 миллионов
		}
	}

	static function DescrSot(&$in, $raz, $ar_descr, $fem = false){
		$ret = '';
	
		$conv = intval($in / $raz);
		$in %= $raz;
		
		if(!empty($ar_descr)) $descr = $ar_descr[ self::DescrIdx($conv%100) ];
		else $descr = '';
		
		if($conv >= 100){
			$Sot = array('сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
			$ret = $Sot[intval($conv/100) - 1] . ' ';
			$conv %= 100;
		}
		
		if($conv >= 10){
			$i = intval($conv / 10);
			if($i == 1){
				$DesEd = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать' );
				$ret .= $DesEd[ $conv - 10 ] . ' ';
				$ret .= $descr;
				// возвращаемся здесь
				return $ret;
			}
			$Des = array('двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто' );
			$ret .= $Des[$i - 2] . ' ';
		}
		
		$i = $conv % 10;
		if($i > 0){
			if( $fem && ($i==1 || $i==2) ){
				// для женского рода (сто одна тысяча)
				$Ed = array('одна', 'две');
				$ret .= $Ed[$i - 1] . ' ';
			}
			else{
				$Ed = array('один', 'два', 'три', 'четыре', 'пять',
				'шесть', 'семь', 'восемь', 'девять' );
				$ret .= $Ed[$i - 1] . ' ';
			}
		}
		$ret .= $descr;
		return $ret;
	}

		
	static function to_text2($sum, $capitalize = false){
	
		$ret = '';
		
		$FracPart = 0;
		$IntPart = 0;
		
		$sum = trim($sum);
		
		// удалим пробелы внутри числа
		$sum = str_replace(' ', '', $sum);
		
		// флаг отрицательного числа
		$sign = false;
		
		if($sum[0] == '-')
		{
			$sum = substr($sum, 1);
			$sign = true;
		}
		
		// заменим запятую на точку, если она есть
		$sum = str_replace(',', '.', $sum);
		
		$IntPart = intval($sum);
		$FracPart = $sum*100 - $IntPart*100;
		
		if($IntPart){
		
			if($IntPart >= 1000000000) $ret .= self::DescrSot($IntPart, 1000000000, array('миллиард', 'миллиарда', 'миллиардов')) . ' ';
				
			if($IntPart >= 1000000) $ret .= self::DescrSot($IntPart, 1000000, array('миллион', 'миллиона', 'миллионов') ) . ' ';
				
			if($IntPart >= 1000) $ret .= self::DescrSot($IntPart, 1000, array('тысяча', 'тысячи', 'тысяч'), true) . ' ';
		
			$ret .= self::DescrSot($IntPart, 1, '');
		

			if($capitalize) $ret = utf8::ucfirst($ret);
		}
		
		// если число было отрицательным добавим минус
		if($sign) $ret = '-' . $ret;
		
		return trim($ret);
	}


	
		
		
		
}

/* ?> */