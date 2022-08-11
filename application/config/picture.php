<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pictures Configs
 */

$config['allowed_types'] = array(
	'image/jpeg', 'image/pjpeg',
	'image/gif', 
	'image/png', 'image/x-png'
);

$config['allowed_types_string'] = 'jpg, png, gif';

$config['file_errors'] = array(
       1=>'Размер файла %s превышает допустимое значение ('. ini_get('upload_max_filesize').').',
       2=>'Размер файла %s превышает допустимое значение.',
       3=>'Файл %s загружен не полностью.',
);

$config['default'] = array(
	'max_amount' => 7,
	'folder' => array(
		1 => array('path' => WEB_ROOT . 'pics/oi', 'url' => '/pics/oi', 'status' => 1),
		),
	'width_min' => 100,
	'height_min' => 100,
	'format' => array(
		'full' => array('suffix' => '', 'width' => '1300', 'height' => '1300', 'method'=>'scale', 'compression' => '70', 'blur' => '0.95', 'watermark' => WEB_ROOT.'i/watermark.png'),
		'mid' => array('suffix' => '_m', 'width' => '800', 'height' => '800', 'method'=>'scale', 'compression' => '70', 'blur' => '0.95'),
		//'thumb' => array('suffix' => '_t', 'width' => '200', 'height' => '200', 'method' => 'scale'/*'crop'*/, 'compression' => '70', 'blur' => '0.85', 'composeWithImage' => (WEB_ROOT . 'i/wdot.gif'), 'addPadding' => 'white'),
		'thumb' => array('suffix' => '_t', 'width' => '400', 'height' => '400', 'method' => 'scale'/*'crop'*/, 'compression' => '70', 'blur' => '0.85'),
		),
);

$config['offer'] = $config['default'];

$config['ro'] = array(
	'max_amount' => 1,
	'folder' => array(
		1 => array('path' => WEB_ROOT . 'pics/ro', 'url' => '/pics/ro', 'status' => 1),
		),
	'width_min' => 230,
	'height_min' => 120,
	'format' => array(
		'full' => array('suffix' => '', 'width' => '230', 'height' => '120', 'method'=>'scale', 'compression' => '85', 'blur' => '0.95'),//, 'watermark' => WEB_ROOT.'i/watermark.png'),
//		'thumb' => array('suffix' => '_t', 'width' => '230', 'height' => '120', 'method'=>'scale', 'compression' => '85', 'blur' => '0.9'),
//		'thumb' => array('suffix' => '_t', 'width' => '100', 'height' => '60', 'method' => 'scale'/*'crop'*/, 'composeWithImage' => (WEB_ROOT . 'i/wdot.gif'), 'addPadding' => 'white', 'compression' => '94', 'blur' => '0.85'),	
		),
);

/* ?> */