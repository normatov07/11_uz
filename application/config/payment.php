<?php defined('SYSPATH') or die('No direct script access.');

$config['ue'] = 'usd';
$config['main_currency'] = 'uzs';
$config['maximum_discount'] = '90';

$config['service'] = array(
	'bonus' => array(
		'title' => 'Покупка бонусов',
		'details' => 'Приобретение',
		'amount' => 1,
		'unit' => 'bonus',
		'description' => ''
	),
	'position' => array(
		'title' => 'Поднятие объявления',
		'details' => 'Поднятие',
		'description' => '<b>Поднятие объявлений:</b> Ваше объявление будет перемещено на самый вверх в общем списке объявлений. Рекомендуем использовать, если Вы разместили объявление более 2-3-х дней тому назад. <a href="/help/#position">Подробная справка</a>.',
	),
	'mark' => array(
		'title' => 'Выделение объявления',
		'details' => 'Выделение',
		'amount' => 2,
		'unit' => 'day',
		'description' => '<b>Выделение объявлений:</b> Ваше объявление будет выделено жёлтым цветом в общем блоке объявлений.',
	),
	'premium' => array(
		'title' => 'Премирование объявления',
		'details' => 'Премирование',
		'amount' => 2,
		'unit' => 'day',
		'description' => '<b>Премиум объявления:</b> Ваше объявление будет размещено в блоке «Премирум объявления» и выделенно цветом. Если вы премируете объявление сроком более чем на 5 дней, то каждые 5 дней объявление автоматически будет подниматься наверх (в блоке премиум объявлений). <a href="/help/#premium">Подробная справка</a>.',
	),
);

$config['default_service'] = 'position';

$config['max_amount_count'] = 10;

$config['method'] = array(
	'free' => array(
		'title' => 'Подарок',
		'notification' => true,
		'discount_enabled' => false
	),
	'bonus' => array(
		'title' => 'Бонусы',
		'currency' => 'bonus',
		'description' => '',
		'notification' => false,
		'discount_enabled' => false
	),
	'ekarmon' => array(
		'title' => 'eKarmon',
		'currency' => 'ue',
		'convert' => true,
		'description' => '<b>eKarmon</b> — это система интернет-платежей, позволяющая участникам электронной коммерции моментально приобретать/реализовывать или оказывать товары/услуги посредством сети Интернет.<br>
			<a href="http://www.ekarmon.uz">Подробная информация об eKarmon.</a>',
		'notification' => true,

		'paymentURL' => (IN_PRODUCTION?'https://payment.ekarmon.uz/makePayment':'https://test.ekarmon.uz:9443/eKPayment/makePayment'),
		'keyFileName' => (IN_PRODUCTION?'zoruz':'shop10643'),
		'keyPassword' => (IN_PRODUCTION?'j53IFc6cWqUVIsXW':'test'),
		'shopID' => (IN_PRODUCTION?'19455':'10643'),


		'in_descriptions' => true,
		'logo' => '/i/ps_ekarmon.png',
		'full_description' => '<a href="http://www.ekarmon.uz">eKarmon</a> — это система интернет-платежей, позволяющая участникам электронной коммерции моментально приобретать/реализовывать или оказывать товары/услуги посредством сети Интернет.',
		'url' => 'http://www.ekarmon.uz',
		'discount_enabled' => true

	),
	'wmy' => array(
		'title' => 'WebMoney',
		'currency' => 'wmy',
		'description' => '',
		'discount_enabled' => true
	),
	'paynet' => array(
		'title' => 'PAYNET',
		'currency' => 'uzs',
		'description' => '<a href="http://www.paynet.uz">PAYNET</a> - это система приема платежей, дествующая на территории всей республики, она функционирует в режиме реального времени 24 часа в сутки, 7 дней в неделю, без выходных и перерывов на обед.',
		'url' => 'http://www.paynet.uz',
		'logo' => '/i/ps-paynet.png',
		'discount_enabled' => false,

		'allowed_ip_mask' => array(
			'213.230.106.112/28', //paynet
			'213.230.65.85/28', //paynet
			'213.230.76.115/28', //paynet
			'127.0.0.1/24', //localhost
			'178.218.201.98/28', //localhost
			'89.146.110.203/24', //my
			'89.236.193.170/24',
			'213.206.43.253/24', //my
			'172.16.100.2/24',
			'178.218.200.0/21',
			'185.8.212.70/24', //office workplace
			'185.8.212.70/24', //office workplace
		),
		'username' => 'paynet',
		'password' => 'GtFk7i8C',
		'notification' => true,
	),
);

$config['unit'] = array(
	'amount' => array('сум', 'сум', 'сумов'),
	'bonus' => array('бонус', 'бонуса', 'бонусов'),
	'day' => array('день', 'дня', 'дней'),
);

$config['currency'] = array(
	'ue' => array('у.е.', 'у.е.', 'у.е.'),
	'uzs' => array('сум', 'сум', 'сум'),
	'usd' => array('USD', 'USD', 'USD'),
	'bonus' => array('бонус', 'бонуса', 'бонусов'),
	'wmy' => array('WMY', 'WMY', 'WMY'),
);

$config['currency_list'] = array(
	'ue' => 'у.е.',
	'uzs' => 'сум'
);

$config['status'] = array(
	'ordered' => 'Ожидает оплаты',
	'complete' => 'Оплачено',
	'expired' => 'Просрочено',
	'cancelled' => 'Отменено',
);

$config['days_to_expiration'] = 3;
$config['days_to_delete_expired'] = 30;

$config['ro_price_onhome'] = '45000';

/* ?> */