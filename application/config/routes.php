<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package  Core
 *
 * Default route to use when no URI segments are available.
 */

$config['_default'] = 'index';
$config['_allowed'] = '(-a-zабвгдеёжзийклмнорстуфхцчшщъыьэюяА-ЯТУФХЦЧШЩЭЮЯ &0-9~%.,:_[]())';
$config['captcha/.+'] = 'captcha';

/**
 * FRONT
 */
//(login|logout|register|activate|lostpass|registration_success|activation_success|lostpass_success|change_password|email_change|email_change_success)

$config['login'] = 'auth/login';

$config['register'] = 'auth/register';
// $config['register'] = 'auth/registration_success';
$config['logout'] = 'auth/logout';
$config['activate'] = 'auth/activate';
// $config['activate'] = 'auth/activation_success';
$config['registration_success'] = 'auth/registration_success';
$config['change_password'] = 'auth/change_password';
$config['lostpass'] = 'auth/lostpass';
$config['change_password'] = 'auth/change_password';

$config['(cat|category)/([\w-]*)(/page/(\d+))?'] = 'category/index/$2/$4'; 
// $config['(category)/([\w-]*)(/page/(\d+))?'] = 'category/index/$2/$4';

$config['type/page/(\d+)'] = 'type/index/0/$1';
$config['type/([\w-]*)(/page/(\d+))?'] = 'type/index/$1/$3';

$config['(rss|export)/(.+?)(\.xml)?'] = '$1/index/$2';
$config['search/(page/(\d+))?'] = 'search/index/$2?';
$config['(offer|offerer|category|news|ro)/([0-9]+)?/?(page/(\d+))?'] = '$1/index/$2/$4';
$config['(offer|offerer)/([0-9]+)/([a-zA-Z_-]+)/?([a-zA-Z0-9_-]*)'] = '$1/$3/$2/$4';
// $config['offer/login'] = 'offer/login';
$config['(my|adm)/payment/page/(\d+)'] = '$1/payment/index/$2';
$config['reklama/([a-z_]+)'] = 'reklama/index/$1';

/**
 * MY
 */
$config['my/?'] = 'my/my/';
$config['my/(settings|offers|bookmarks|messages)/?([a-z_-]*)(/page/(\d+))?'] = 'my/$1/index/$2/$4';

$config['my/(bookmark|message)/([0-9]+)/([a-zA-Z_-]+)/?'] = 'my/$1s/$3/$2';
$config['my/(message)/?([0-9]*)'] = 'my/$1s/reply/$2';

$config['contacts/success'] = 'contacts/index/success';
$config['contacts/realtor'] = 'contacts/index/realtor';

/**
 * ADM
 */
$config['adm'] = 'adm/adm';
$config['adm/(offers|sms)/?(page/(\d+))?'] = 'adm/$1/index/$3';
$config['adm/(news|user|bonus|category|list|type|region|exporter)/([0-9]+)?/?(page/(\d+))?'] = 'adm/$1/index/$2/$4';
$config['adm/user/([0-9]+)/delete_cert_file/(certificate|license|other)'] = 'adm/user/delete_cert_file/$1/$2';
$config['adm/ro/page/(\d+)'] = 'adm/ro/index/$1';
$config['adm/ro/(client|agent)/([\d]+)/page/(\d+)'] = 'adm/ro/$1/$2/$3';
/* ?> */

Router::$current_uri = ($_SERVER['REQUEST_URI'] === '/') ? $config['_default'] : $_SERVER['REQUEST_URI'];

// $uri = clearRequestUri();

// foreach ($config as $key => $val){
//     if(preg_match('#^'.$key.'$#',$uri)){
//         Router::$current_uri = $val;
//         break;
//     }
// }


// function clearRequestUri()
// {
//    $pattern = "/\?.*/i";
//    $uri = $_SERVER['REQUEST_URI'];
//    $uri = preg_replace($pattern ,"",$uri);
//    $uri = trim($uri);
//    $uri = trim($uri, '/');
//    return $uri;
// }