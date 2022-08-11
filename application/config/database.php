<?php defined('SYSPATH') or die('No direct script access.');


$isLocal = $_SERVER['HTTP_HOST'] == 'aliexpress.loc';

$config['default'] = array
(
    'benchmark'     => TRUE,
    'persistent'    => TRUE,
    'connection'    => array
    (
        'type'     => 'mysqli',
        'user'     => $isLocal ? 'flaer_aliexpress' : 'root',
        'pass'     => 'Root@1999',
        'host'     => 'localhost',
        'port'     => FALSE,
        'socket'   => FALSE,
        'database' => 'alibaba_db'
    ),
    'character_set' => 'utf8',
    'table_prefix'  => '',
    'object'        => TRUE,
    'cache'         => TRUE,
    'escape'        => TRUE
);

//database : ch13154_srcdoza
//pass : ts7ZBAJ4