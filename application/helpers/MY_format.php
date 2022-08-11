<?php defined('SYSPATH') or die('No direct script access.');

class format extends format_Core{

	
	public static function phone($number, $del = ',', $nowrap = FALSE){
		
	
			if (empty($number)) return '';
			
			if(preg_match('/[\d-]{6,}\s+[\d-]{6,}/', $number)):
				$numbers = preg_split('/\s+/', $number);
			else:
				if ($del === NULL) $del = ',';

				$numbers = preg_split('/'.$del.'/', $number);
			endif;

			if(count($numbers) > 1):

				$output = array();
				
				foreach($numbers as $number):
					$output[] = self::phone($number, NULL, $nowrap);
				endforeach;
				
				return join(', ', $output);
				
			else:
				
				$number = preg_replace('/\D/','',$number);

				$len = strlen($number);

				$numberParts = array();
				
				if($len == 5):
					sscanf($number, "%2s%3s", $numberParts[6], $numberParts[7]);
				elseif ($len == 6):
					sscanf($number, "%2s%2s%2s", $numberParts[5], $numberParts[6], $numberParts[7]);
				elseif ($len == 7):
					sscanf($number, "%3s%2s%2s", $numberParts[5], $numberParts[6], $numberParts[7]);
				elseif ($len == 12):
					sscanf($number, "%3s%2s%3s%2s%2s", $numberParts[2], $numberParts[3], $numberParts[5], $numberParts[6], $numberParts[7]);
				else: // (!preg_match('/^((\d*)(\D?)(\d{1})(\d{2}))(\d{2})(\d{2})$/', $number, $numberParts)):
					return $number;
				endif;
				
				$output =
					  (strlen(@$numberParts[2]) ? '+' . $numberParts[2].' ' : '')
					. (strlen(@$numberParts[3]) ? '(' . $numberParts[3] . ') ' : '')
					. (strlen(@$numberParts[4]) ? $numberParts[4] . ' ' : '')
					. (strlen(@$numberParts[5]) ? @$numberParts[5] . '-'  : '')
					. @$numberParts[6]. '-'.@$numberParts[7];
				
			endif;		
			
			if($nowrap) return '<span class="nw">'.$output.'</span>';
			
			return $output;
	}
	
	public static function rawphone($number)
	{				
		
		$number = preg_replace('/\s/u', ' ', $number);
		$number = preg_replace('/[^\d\s,]/','',$number);
		$number = preg_replace('/(^|\s+)(\d{0,5})(\s+|$|(,))/','$2$4',$number);
		$number = preg_replace('/(^|\s+)(\d{0,5})(\s+|$|(,))/','$2$4',$number);
		
		if(preg_match('/^\d+$/', $number) and (strlen($number)%7) == 0):			
			$number = implode(',', str_split($number, 7));
		elseif(preg_match('/[\d]{6,}[\D]+[\d]{6,}/', $number)):
			$number = implode(',', preg_split('/\D+/', $number));		
		endif;
		
		return $number;		

	}

	public static function rawmoney($number)
	{
	
		if(preg_match('/^(.*?)([,.])(.{1,2})$/',trim($number), $matches)):
			return preg_replace('/\D/','',$matches[1]) .'.'. preg_replace('/\D/','',$matches[3]);
		endif;
		
		return preg_replace('/\D/','',trim($number));
		
	}
	
	  /**
      * Склонение числительных (1 постер, 2 постера, ..., 158 постеров и т.п.
      * @param  int    $num - число для склонения
      * @param  array  $arr - массив с формами слова в именительном (ед.ч и мн.ч) и родительном падежах (мн.ч)
      * @return string      - строка в нужном падеже
      */
     static function declension_numerals($num, $first = NULL, $two2four = NULL, $other = NULL) {

		if(is_array($num)):
			$tag = array_slice($num, 1);
			$num = $num[0];
		endif;
		
		if(empty($first)) return $num;
		
		if(is_array($first)):
			if($two2four == TRUE) $nonum = TRUE;
			$two2four = $first[1];
			$other = $first[2];
			$first = $first[0];
			
		endif;

		if(empty($two2four)) $two2four = $first;
		if(empty($other)) $other = $first;
		
		$last = $num % 10; // последняя цифра
		$end = $other;
		
		if (intval(($num % 100) / 10) != 1) {
			switch($last){
				case 1:
					$end = $first;
					break;
				case 2:
				case 3:
				case 4:
					$end = $two2four;
					break;
				default:
					$end = $other;
			}
		}
		
		if(!empty($tag) and count($tag)):
			$num = $tag[0] . $num . @$tag[1];
		endif;
		
		if(!empty($nonum)):
			return ' ' . $end;
		else:
			return $num . " " . $end;
		endif;
	}
	
	static function number($number, $nospace = false, $del = '.'){
		
		if(!is_numeric($number) and valid::numeric($number)):
			$locale = localeconv();
			$number = preg_replace('/[\\'.$locale['decimal_point'].']/', '.', $number);
		endif;
		
		if($number == round($number)):
			return number_format($number, 0, $del, $nospace?'':' ');
		else:
			return number_format($number, 2, $del, $nospace?'':' ');
		endif;
	}



	static function money($sum, $currency, $boldnum = false){
		if(empty($sum)) return '-';
		
		if(!is_numeric($sum) and !valid::numeric($sum)) return $sum;
		
		$sum = self::number($sum, NULL, ',');
		if($boldnum) $sum = $sum;
		
		switch($currency):
			case 'uzs':
				return $sum.' сум';//self::declension_numerals(self::number($sum), 'сум','сума','сумов');
			break;
			case 'usd':
				return '$'.$sum;
			break;
			case 'eur':
				return '€'.$sum;
			break;
			case 'rub':
				return $sum .' росс. руб.';
			break;
			case 'ue':
				return $sum .' у.е.';
			break;
			case 'wmy':
				return $sum .' WMY';
			break;
			case 'bonus':
				return self::declension_numerals(self::number($sum), 'бонус','бонуса','бонусов');
			break;
			default:
				return $sum .' '. $currency;
			break;
		endswitch;
	}

	function clear_number($str){
		return preg_replace('~\D~','',$str);
	}

		
}

/* ?> */