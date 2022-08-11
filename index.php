<?php

// if($_SERVER['HTTP_HOST'] != "vh326.timeweb.ru" && $_SERVER['REMOTE_ADDR'] != "83.221.161.74"){
//    exit;
// }

// ini_set('display_errors', 1); 
// error_reporting(E_ALL & ~E_NOTICE);

define('IN_PRODUCTION', FALSE);

$kohana_application = 'application';
$kohana_modules = 'modules';
$kohana_system = 'system';

error_reporting(1);
ini_set('display_errors', 1);

define('EXT', '.php');

$kohana_pathinfo = pathinfo(__FILE__);


define('DOCROOT', $kohana_pathinfo['dirname'].DIRECTORY_SEPARATOR);
define('KOHANA',  $kohana_pathinfo['basename']);

// If the front controller is a symlink, change to the real docroot
is_link(KOHANA) and chdir(dirname(realpath(__FILE__)));

// If kohana folders are relative paths, make them absolute.
$kohana_application = file_exists($kohana_application) ? $kohana_application : DOCROOT.$kohana_application;
$kohana_modules = file_exists($kohana_modules) ? $kohana_modules : DOCROOT.$kohana_modules;
$kohana_system = file_exists($kohana_system) ? $kohana_system : DOCROOT.$kohana_system;

// Define application and system paths
define('APPPATH', str_replace('\\', '/', realpath($kohana_application)).'/');
define('MODPATH', str_replace('\\', '/', realpath($kohana_modules)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($kohana_system)).'/');

// Clean up
unset($kohana_application, $kohana_modules, $kohana_system);

require SYSPATH.'core/Bootstrap'.EXT;