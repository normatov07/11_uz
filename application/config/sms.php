<?php defined('SYSPATH') or die('No direct script access.');

$config['enabled'] = true;

$config['default_aggregator'] = 'nikita';

$config['aggregators'] = array(
	'nikita' => array(
		
		'password' => '',
		'IP' => '',

		'response_is_sync' => false,
				
		'providers' => 'UMS, UCell, Билайн',
		
		'service_expiration' => '2 дня', //отправляется в письме в родительном падеже
		
		'notifications_status' => true,		
		'notification_interval' => 6, // часов между отправкой сообщений
		'notifications_total' => 6, // количество сообщений
		
		'max_sms_per_user_in_one_hour' => 20,
		
		'text_limit' => 100,
		
		'service' => array(
			'position' => array('keyword' => 'top', 'price' => 1.20, 'currency' => 'usd', 'short_number' => '4161'),
			'premium' => array('keyword' => 'pr', 'price' => 1.20, 'currency' => 'usd', 'short_number' => '4161'),
			'mark' => array('keyword' => 'mark', 'price' => 1.20, 'currency' => 'usd', 'short_number' => '4161'),
		),
	),
	'smsbroker' => array(
		
		'password' => '',
		'IP' => '',
	
		'response_is_sync' => true,
	
		'providers' => 'МТС, Билайн, Мегафон',
		
		'service_expiration' => '3 дней', //отправляется в письме в родительном падеже
		'notifications_status' => false,
		'notification_interval' => 6, // часов между отправкой сообщений
		'notifications_total' => 6, // количество сообщений
		
		'max_sms_per_user_in_one_hour' => 20,
		
		'text_limit' => 100,
		
		'service' => array(
			'position' => array('keyword' => '70+14', 'price' => 0.90, 'currency' => 'usd', 'short_number' => '7099'),
			'mark' => array('keyword' => '70+15', 'price' => 0.90, 'currency' => 'usd', 'short_number' => '7099'),
			'premium' => array('keyword' => 16, 'price' => 1.30, 'currency' => 'usd', 'short_number' => '2893'),
		),

	),
);

$config['transliterate'] = true;

$config['text'] = array(
	'invalid_keyword' => '$app: Неверно задано ключевое слово: $keyword',
	'error_no_offer' => '$app: Объявления #$offer не найдено!',
	'offer_notification' => '$app: Объявление #$offer: ">$title<" - новых сообщений $messages, всего просмотров $views',
);

$config['service'] = array(
	'position' => array(
		'title' => 'Поднятие объявления',
		'details' => 'Поднятие',
		'description' => 'Ваше объявление будет поднято наверх в списке обычных объявлений',
		'confirmation' => '$app: Объявление #$offer поднято'
	),	
	'mark' => array(
		'title' => 'Выделение объявления',
		'details' => 'Выделение',
		'amount' => 2,
		'unit' => 'day',
		'description' => 'Ваше объявление будет выделено цветом в списке обычных объявлений на 2 дня',
		'confirmation' => '$app: Объявление #$offer выделено до $till'
	),
	'premium' => array(
		'title' => 'Премирование объявления',
		'details' => 'Премирование',		
		'amount' => 2,
		'unit' => 'day',
		'description' => 'Ваше объявление будет перенесено в список премиум объявлений на 1 день.',
		'confirmation' => '$app: Объявление #$offer премировано до $till'
	),
);


$config['status'] = array(
	'requested' => 'Запрошено',
	'replied' => 'Отвечено',
	'complete' => 'Отработан',	
);


/* ?> */
