<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Библиотека обработки SMS
 */	
class Sms_Core {

/**
 * Функция получения экземпляра объекта аггрегатора
 *
 * @param string $aggregator - ID аггрегатора
 * @return object or null
 */	
	public function getAggregator($aggregator) {
		
		$aggregator = utf8::strtolower($aggregator);
	
		switch($aggregator):
			case 'NIKITA':
			case 'nikita':
				require_once('Sms/NikitaSmsAggregator.php');
				return new NikitaSmsAggregator();
			break;
			case 'SMSBROKER':
			case 'smsbroker':
				require_once('Sms/SmsbrokerSmsAggregator.php');
				return new SmsbrokerSmsAggregator();
			break;
		endswitch;

		return NULL;
	}
	
/**
 * Функция получения параметров аггрегатора
 *
 * @param string $aggregator - ID аггрегатора
 * @return array
 */	
	public function getConfig($aggregator = NULL) {
		if(empty($aggregator)) $aggregator = Lib::config('sms.default_aggregator');
		return Lib::config('sms.aggregators', $aggregator);
	}

/**
 * Функция получения сервиса по ключевому слову для аггрегатора
 *
 * @param string $aggregator - ID аггрегатора
 * @param string $keyword
 * @return array
 */	
	public function getServiceByKeyword($aggregator = NULL, $keyword = NULL) {
		
		$config = self::getConfig($aggregator);
		
		foreach($config['service'] as $service => $params)
		{
			if($params['keyword'] == $keyword)
			{					
				return $service;
			}
		}
		
	}	
	
/**
 * Функция подготовки текста SMS
 *
 * @param string $text - текст сообщения
 * @param array $replacements - массив слов на замену
 * @param array $params - необязательный массив дополнительных параметров
 * @return string
 */		
	public function prepareMessage($text, $replacements, $params = array()){
	
		if(!isset($params['transliterate'])) $params['transliterate'] = Lib::config('sms.transliterate');
		
		$tr = array('$app' => Lib::config('app.title'));
		
		foreach($replacements as $key => $val)
			$tr['$'. $key] = $val;
					
		$text = strtr($text, $tr);	

		if($params['transliterate']) $text = text::transliterate($text);
/**
 * shrink long values
 */	
		if(preg_match('/>([^<]+)</iu', $text, $match)):
		
			$replace = utf8::substr($match[1], 0, $params['text_limit'] - utf8::strlen($text) - 2);
			if($match[1] != $replace) $replace .= '...';

			$text = str_replace($match[0], $replace, $text);
			
		endif;
		
		if(!empty($params['text_limit'])) return utf8::substr($text, 0, $params['text_limit']);
		
		return $text;
	
	}
}

/* ?> */