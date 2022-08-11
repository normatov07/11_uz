<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Application Specific Configs
 */
$config['title'] = '11.uz';
$config['subtitle'] = '';

if(!defined('WEB_ROOT')) define('WEB_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'],'/') .'/');
if(empty($_SERVER['SERVER_NAME'])) $_SERVER['SERVER_NAME'] = '11.uz';

$config['WEB_ROOT'] = WEB_ROOT;
$config['url'] = 'http://'.$_SERVER['SERVER_NAME'];
@define('PAGE_URL', $config['url'].$_SERVER['REQUEST_URI']);

/**
 * Use GZIP compressed media files (css, js, etc...)
 */
$config['USE_GZIP'] = false;
$config['CHECK_FOR_GZIPPED_FILES_EXISTENCE'] = true;
$config['GZIP_SUFFIX'] = '.gz';

/**
 * Use media files dirs
 */
$config['JS_DIR'] = '/js';
$config['CSS_DIR'] = '/css';
$config['USER_CERT_DIR'] = $_SERVER['DOCUMENT_ROOT'].'/cert';


$config['default_js'] = array(
	'jquery.min.js',
	'jquery.form.js',
	'main.js',
	'search.js'
);

$config['default_css'] = array(
	'ionicons.min.css',
	'main_new.css?new2019'
);


/**
 * Use profiler
 */
$config['enableProfiler'] = !IN_PRODUCTION;

/**
 * disable baseurl
 */
$config['disable_baseurl'] = false;

/**
 * LIMITS
 */

$config['start_year'] = 2009;

$config['offers_of_each_type_on_homepage'] = 36;
$config['other_offers_offer_view_page'] = 4;

$config['region_select_enabled'] = true;
/**
 * Expiration days
 */

$config['offer_expiration_days'] = 30; // дней до отключения объявления
$config['offer_enable_expiration_days_plus'] = 5; // дней добавить при включении просроченного объявления

$config['offer_expiration_notification_days'] = 3; // за сколько дней напоминать об истекающий объявлениях
$config['offer_marked_expiring_notification_days'] = 1; // за сколько дней напоминать об истекающий обозначенных объявлениях
$config['offer_premium_expiring_notification_days'] = 1; // за сколько дней напоминать об истекающий премиум объявлениях


$config['premium_position_days'] = 5;

$config['not_activated_user_expiration_days'] = 3; // дней до стирания не активированного аккаунта

$config['offer_is_dublicate_days'] = 60; // минут в течение которых объявление от одного и того же пользователя будет дубликатом

$config['message_alive_days'] = 180;

/**
 * STATUSES
 */

$config['offer_status'] = array(
	'disabled' => 'Отключено',
	'enabled' => 'Активно',
	'banned' => 'Заблокировано',
	'expired' => 'Просрочено',
	'deleted' => 'Удалено',
	'user_banned' => 'Заблокирована Учетная запись'
);

$config['user_status'] = array(
	'disabled' => 'Не активирован',
	'enabled' => 'Активирован',
	'banned' => 'Заблокирован',
);

/**
 * USER ROLES
 */

$config['user_roles'] = array(
	'general' => 'Частное лицо',
	'company' => 'Организация',
	'moderator' => 'Модератор',
	'administrator' => 'Администратор',
	'agent' => 'Рекламный агент',
	'realtor' => 'Риелтор',
);

$config['user_ban_reasons'] = array(
	'' => 'Без указания причины',
	'cyrillic_only' => 'Все данные на русском языке должны быть заполнены кириллицей',
	'incorrect_name' => 'Укажите правильно ваши ФИО',
	'incorrect_title' => 'Укажите правильно название вашей Организации',
	'other' => 'Указать отдельно',
);

/**
 * E-MAIL RELATED CONFIG
 */

// почтовый адрес администратора для уведомлений
$config['admin_email'] = 'contact@ali-baba.uz';

// организация/лицо, от имени которого отправляются письма (необязательно)
$config['email_name_from'] = 'AliBaba';

// адрес From для отправляемых писем (см. email_name_from)
$config['email_address_from'] = 'info@ali-baba.uz';

// префикс для темы письма
$config['email_subject_prefix'] = 'AliBaba: ';

// адрес для жалоб
$config['email_complaint_address'] = 'info@ali-baba.uz';

// адрес для жалоб
$config['email_contact_address'] = 'info@ali-baba.uz';

/**
 * CONSTANT LISTS
 */
$config['currencies'] = array(
	'ue' => 'у.е.',
	'uzs' => 'сум'
);

$config['price_type'] = array(
//	'' => 'не указана',
	'fixed' => 'фиксированная',
	'from-to' => 'от... до...',
	'negotiated' => 'договорная'
);

$config['declensions'] = array(
	'offer' => array('объявление', 'объявления', 'объявлений')
);

$config['complaint_titles'] = array(
	'incorrect' => 'Объявление содержит некорректные данные',
	'dirty' => 'Объявление оскорбительного характера',
	'fraud' => 'Объявление является обманом',
	'advertisement' => 'Объявление является рекламой',
	'antireklama' => 'Объявление содержит антирекламу',
	'other' => 'Другое',
);

$config['ban_reasons'] = array(
	'' => 'Без указания причины',
	'dublicate' => 'Объявление дублирует Ваше предыдущее объявление',
	'incorrect' => 'Объявление содержит некорректные данные',
	'illegal' => 'Объявление нарушает Условия и Правила ',
	'antireklama' => 'Объявление содержит антирекламу',
	'other' => 'Указать отдельно причину',
);

$config['remove_reasons'] = array(
	'' => 'Без указания причины',
	'dublicate' => 'Объявление дублирует Ваше предыдущее объявление',
	'incorrect' => 'Объявление содержит некорректные данные',
	'illegal' => 'Объявление нарушает Условия и Правила ',
	'antireklama' => 'Объявление содержит антирекламу',
	'other' => 'Указать отдельно причину',
);


$config['periods'] = array(
	'' => 'любой',
	'1' => 'за день',
	'7' => 'за неделю',
	'31' => 'за месяц',
	'61' => 'за два месяца'
);

$config['district_type'] = array(
	'' => 'Нет',
	'city' => 'Город',
	'district' => 'Район'
);

/**
 * Период ограничения отправки сообщений в минутах
 * @var integer
 */
$config['messages_limit_period'] = 600;

/**
 * Ограничение количества сообщений за период
 * @var integer
 */
$config['messages_limit_amount'] = 10;

/**
 * Срок удаления старых объявлений в днях
 * @var integer
 */
$config['offer_days_to_be_deleted'] = 90;
$config['offer_deletion_operation_max_time'] = 28;

/* ?> */