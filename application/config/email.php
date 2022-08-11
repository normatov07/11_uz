<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SwiftMailer driver, used with the email helper.
 *
 * @see http://www.swiftmailer.org/wikidocs/v3/connections/nativemail
 * @see http://www.swiftmailer.org/wikidocs/v3/connections/sendmail
 * @see http://www.swiftmailer.org/wikidocs/v3/connections/smtp
 *
 * Valid drivers are: native, sendmail, smtp
 */
if(IN_PRODUCTION) $config['driver'] = 'native';
else $config['driver'] = 'smtp';

/**
 * To use secure connections with SMTP, set "port" to 465 instead of 25.
 * To enable TLS, set "encryption" to "tls".
 *
 * Driver options:
 * @param   null    native: no options
 * @param   string  sendmail: executable path, with -bs or equivalent attached
 * @param   array   smtp: hostname, (username), (password), (port), (auth), (encryption)
 */

if(IN_PRODUCTION) $config['options'] = NULL;
else $config['options'] = array('hostname'=>'afishamedia.uz', 'username' => 'tech@afishamedia.uz', 'password' => 'zrrcbqvmda');

// Принудительная отправка почты через SMTP
//$config['driver'] = 'smtp';
//array('hostname'=>'smtp.sarkor.uz', 'username' => 'afishamedia', 'password' => 'Eejahz7e');