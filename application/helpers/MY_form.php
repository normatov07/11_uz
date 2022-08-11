<?php defined('SYSPATH') or die('No direct script access.');

class form extends form_Core{

	public static function checked($current_value, $default_value = 1, $checked_by_default = FALSE){
		if((!isset($current_value) and $checked_by_default) or $current_value == $default_value) return ' checked="checked"';
	}
	public static function selected($current_value, $default_value = 1, $is_default = FALSE){
		if(($current_value == '' and !empty($is_default)) or $current_value == $default_value) return ' selected="selected"';
	}	
	
	public static function stringToForm($str){
		if(!empty($str)):
			$str = preg_replace('~<br( /)?>\n*~',"\n", $str);
			
			$str = preg_replace('~</p>\n*~',"\n\n", $str);
			$str = strip_tags($str);
		endif;
		return $str;
	}
	
	public static function value($value, $obj = NULL, $obj2 = NULL){
		
		if(!empty($obj) and is_object($obj)) $value = $obj->$value;
		elseif(!empty($obj2) and is_object($obj2)) $value = $obj2->$value;
		
		return html::specialchars(text::untypography($value));
	}
	
		
	public static function radio_list($array = array(), $data, $value = '', $extra = ''){
		if ( ! is_array($data)){
			$data = array('name' => $data);
		}
		if(empty($data['id'])){
			$data['id'] = $data['name'];
		}
		if(empty($data['delimiter'])){
			$delimiter = "\n";
		}else{
			$delimiter = $data['delimiter'];
		}
		
		unset($data['delimiter']);
		
		$output = array();
		$i = 0;
		$id = $data['id'];
		foreach($array as $key => $item):
			$data['id'] = $id.$i;			
			$data['title'] = $item;
			$output[] = '<label for="'.$data['id'].'">'. form::radio($data, $key, $value == $key, $extra) . ' ' . $item.'</label>';
			$i++;
		endforeach;
		
		return join($delimiter, $output);
	}
	
	public static function checkbox_list($array = array(), $data, $values = array(), $extra = ''){
		if ( ! is_array($data)){
			$data = array('name' => $data);
		}
		if(empty($data['id'])){
			$data['id'] = $data['name'];
		}
		if(empty($data['delimiter'])){
			$delimiter = "\n";
		}else{
			$delimiter = $data['delimiter'];
		}
		
		unset($data['delimiter']);
		
		if(!is_array($values)):
			$values = array($values);
		endif;
		
		$output = array();
		$i = 0;
		$count = 0;
		$id = $data['id'];
		
		$data['name'] = $data['name'].'[]';

		if(!empty($data['splittocols']) and (empty($data['minincol']) or $data['minincol'] <= count($array))):
			
			$incol = ceil(count($array)/$data['splittocols']);
			$delimiter = "\n";
			$output[$count++] = "<div class=\"checklist\">\n<ul>";
			if(!empty($data['minincol'])) unset($data['minincol']);
			unset($data['splittocols']);
		endif;
		
		foreach($array as $key => $item):
			$data['id'] = $id.$i;
			$data['title'] = $item;
			
			$output[$count] = '<label for="'.$data['id'].'">'. form::checkbox($data, $key, (!empty($values) and in_array($key, $values)), $extra) . ' ' . $item.'</label>';

			if(!empty($incol)):
				$output[$count] = '<li>' . $output[$count] . '</li>';
				if($count > 1 and ($count-1)%$incol == 0) $output[$count] = "\n</ul>\n<ul>\n" . $output[$count];
			endif;				
			
			$i++; $count++;
		endforeach;
		
		if(!empty($incol)) $output[$count] = '</ul></div>';
	
		return join($delimiter, $output);
	}	
	
	public static function yesno($data, $value = 1, $extra = ''){
	
		if ( ! is_array($data)){
			$data = array('name' => $data);
		}
		
		if(empty($data['id'])){
			$data['id'] = $data['name'];
		}
		
		if(empty($data['class'])) $data['class'] = 'r';
		
		$id = $data['id'];
		$data['id'] = $id.'_yes';
		$field = form::radio($data, '1', $value == 1, $extra);
		$field .= form::label($id . '_yes', ' Да ', $extra) . ' &nbsp; ';
		$data['id'] = $id.'_no';
		$field .= form::radio($data, '0', $value == 0, $extra);
		$field .= form::label($id . '_no', ' Нет ', $extra);
		
		return $field;
	}
	
	private static $days = array();
	private static $years = array();

	private static $months = array(
				'' => 'Месяц',
				1 => 'Январь',
				'Февраль',
				'Март',
				'Апрель',
				'Май',
				'Июнь',
				'Июль',
				'Август',
				'Сентябрь',
				'Октябрь',
				'Ноябрь',
				'Декабрь'
			);		
			
	public static function dateSelect($data, $value = '', $extra = ''){
		if ( ! is_array($data)){
			$data = array('name' => $data);
		}
		
		if(empty($data['id'])){
			$data['id'] = preg_replace('![^a-z_-]!i', '', $data['name']);
		}

		if(empty($data['startyear'])) $data['startyear'] = date('Y');
		if(empty($data['endyear'])) $data['endyear'] = 1935;
		
		
		if(!count(self::$days)):
			self::$days = array(''=>'День');
			for($i=1;$i<=31;$i++) self::$days[$i] = $i;
		endif;
		
		if(!count(self::$years)):
			self::$years = array(''=>'Год');
			for($i=$data['startyear'];$i>=$data['endyear'];$i--) self::$years[$i] = $i;
		endif;
		
		if(!empty($value)) $date = explode('-', $value);

		unset($data['startyear'], $data['endyear']);
		
		$input = form::dropdown(array('name'=>$data['name'].'[year]', 'title'=>'Год'), self::$years, @$date[0]);
		$input .= ' '.form::dropdown(array('name'=>$data['name'].'[month]', 'title'=>'Месяц'), self::$months, (int) @$date[1]);
		$input .= ' '.form::dropdown(array('name'=>$data['name'].'[day]', 'title'=>'День'), self::$days, (int) @$date[2]);
		
		return $input;
		
	}
	
		
	public function digiDate($data, $value = '', $extra = ''){
	
		if ( ! is_array($data)){
			$data = array('name' => $data);
		}
		
		if(empty($data['id'])){
			$data['id'] = preg_replace('![^a-z_-]!i', '', $data['name']);
		}
		
		if(!empty($value)):
			$value .= mb_substr('ГГГГ-ММ-ДД', mb_strlen($value),10);			
			$date = explode('-', $value);
			$date[2] = substr($date[2],0,2);
		else:
			$date = array((!empty($data['monthselect'])?'Год':'ГГГГ'),'ММ',(!empty($data['monthselect'])?'День':'ДД'));
		endif;

		$input = '<span class="digidate"' . (!empty($data['style'])?' style="' . $data['style'] . '"':'') . '>';
		$input .= form::input(array('name'=>$data['name'].'[year]', 'id'=>$data['id'].'year', 'maxlength'=>'4', 'class'=>'y', 'title'=>(!empty($data['monthselect'])?'Год':'ГГГГ')), @$date[0]);
		if(!empty($data['monthselect'])):
			$input .= form::dropdown(array('name'=>$data['name'].'[month]', 'id'=>$data['id'].'month', 'title'=>'Месяц'), self::$months, (int) @$date[1]);
		else:
			$input .= form::input(array('name'=>$data['name'].'[month]', 'id'=>$data['id'].'month', 'maxlength'=>'2', 'class'=>'m', 'title'=>'ММ'), @$date[1]);
		endif;			
		if(empty($data['noday'])) $input .= form::input(array('name'=>$data['name'].'[day]', 'id'=>$data['id'].'day', 'maxlength'=>'2', 'class'=>'d', 'title'=>(!empty($data['monthselect'])?'День':'ДД')), @$date[2]);
		$input .= '</span>';
		
		return $input;
		
	}


	public static function digiDateTimeSelect($data, $value = '', $extra = ''){
	
		if ( ! is_array($data)){
			$data = array('name' => $data);
		}
		
		if(empty($data['id'])){
			$data['id'] = preg_replace('![^a-z_-]!i', '', $data['name']);
		}

		if(!empty($value)):
			$value .= mb_substr('ГГГГ-ММ-ДД ЧЧ:ММ', mb_strlen($value),16);
			$value = explode(' ', $value);
			$date = explode('-', $value[0]);
			$time = explode(':', $value[1]);
		else:
			$date = array('ГГГГ','ММ','ДД');
			$time = array('ЧЧ','ММ');
		endif;

		$input = '<div class="digidatetime"' . (!empty($data['style'])?' style="' . $data['style'] . '"':'') . '>';
		$input .= form::input(array('name'=>$data['name'].'[year]', 'id'=>$data['id'].'year', 'maxlength'=>'4', 'class'=>'y', 'title'=>'ГГГГ'), @$date[0]);
		$input .= ' '.form::dropdown(array('name'=>$data['name'].'[month]', 'title'=>'Месяц'), self::$months, (int) @$date[1]);
		$input .= form::input(array('name'=>$data['name'].'[day]', 'id'=>$data['id'].'day', 'maxlength'=>'2', 'class'=>'d', 'title'=>'ДД'), @$date[2]);
		$input .= ' '.form::input(array('name'=>$data['name'].'[hour]', 'id'=>$data['id'].'hour', 'maxlength'=>'2', 'class'=>'d', 'title'=>'ЧЧ'), @$time[0]);
		$input .= ':'.form::input(array('name'=>$data['name'].'[minute]', 'id'=>$data['id'].'minute', 'maxlength'=>'2', 'class'=>'d', 'title'=>'ММ'), @$time[1]);
		$input .= '</div>';		
		
		return $input;
		
	}
	

/**
 * Amount of days selection 
 */

	public function daysAmountSelect($data, $value = '', $extra = ''){
	
		if(!empty($data['startwithzero'])) $i = 0; else $i = 1;
		
		unset($data['startwithzero']);
		
		for($i = $i; $i < (!empty($data['max'])?$data['max']:31); $i++):
			$days[$i] = format::declension_numerals($i, 'день', 'дня', 'дней');
		endfor;
		
		unset($data['max']);			
		
		return form::dropdown($data, $days, $value, $extra='');
	}
	
}

/* ?> */