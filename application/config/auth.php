<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Authentification Configs
 */
$config['log_enabled'] = true;

$config['activation_key_length'] = 32;

$config['generated_password_length'] = 10;

//---------------------------------------------------
// НАСТРОЙКИ UserSession
//---------------------------------------------------

$config['us_cookie_name'] = 'session_id';
$config['us_admin_cookie_name'] = 'pa_session';
$config['us_expiration'] = 2;  // время жизни сессии (часы)
$config['us_user_key_expire'] = 120; // время жизни пароля сессии (дни)
$config['us_encryption_key'] = '5g67cPGEpG67HpIvR14I6yBo5esnN2'; // обязательно для заполнения
$config['us_gc_chance'] = 10; // вероятность вызова процедуры сборки мусора (в процентах)
$config['us_match_ip'] = FALSE;
$config['us_match_user_agent'] = TRUE;

/**
 * EMAIL configuration
 */

// тема и шаблон письма активации
$config['activation_email_subject'] = 'Подтверждение регистрации на сайте';
$config['activation_email_tpl'] = 'auth/email/activation_view';

// тема и шаблон письма письма для восстановления пароля
$config['lostpass_email_subject'] = 'Восстановление пароля на сайте';
$config['lostpass_email_tpl'] = 'auth/email/lostpass_view';
/* ?> */