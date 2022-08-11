<?php
require_once(dirname(__FILE__) . '/SmsAggregator.interface.php');

//class SmsRequest{}

class NikitaSmsAggregator implements SmsAggregator {

	const LOGIN = 'zor';
	const PASSWORD = 'Ran9t7h';

//	const HOST = '213.59.0.197';

/*  OLD 
	const HOST = 'transport.vas.uz';
	const PORT = '80';
	const PATH = '/send.ashx';
*/

//  from 2014-03-20
	const HOST = '89.236.193.115';
	const PORT = '80';
	const PATH = '/send.ashx';
	
	static $responseStatuses = array(
		'-1' => 'Сообщение не доставлено до абонента.',
		'0' => 'Сообщение успешно отправлено абоненту.',
		'1' => 'Ошибка авторизации.',
		'2' => 'Неверный номер транзакции.',
		'3' => 'Ошибка синтаксиса.',
		'5' => 'Превышено допустимое количество разрешенных сообщений в час на одного абонента. Передается партнеру сразу, в ответ на его запрос об отправке.',
		'6' => 'Достигнуто ограничение на количество разрешенных SMS у партнера. Передается партнеру сразу, в ответ на его запрос об отправке.',
	);

	private $testXML = '<?xml version="1.0"?>
<messages>
	<message>
		<transaction-id>234</transaction-id>
		<time>21-06-2009 12:34:34.123</time>
		<keyword activated="true">mark</keyword>
		<provider>bee</provider>
		<short-number>4161</short-number>
		<phone>998901893599</phone>
		<text>359</text>
		<money>1.20</money>
		<region-id>1</region-id>
	</message>
	<delivery-report>
		<transaction-id>234</transaction-id>
		<time>19-11-2004 12:34:34.125</time>
	</delivery-report>
	<message>
		<transaction-id>345</transaction-id>
		<time>19-06-2009 12:34:34.123</time>
		<keyword activated="true">up</keyword>
		<provider>bee</provider>
		<short-number>4161</short-number>
		<phone>998901893599</phone>
		<text>359</text>
		<money>1.20</money>
		<region-id>1</region-id>
	</message>
</messages>';




	function __construct() {
		if(IN_PRODUCTION and empty($_REQUEST['debug'])) error_reporting(0);
	}


	public function getRequest(){

		header("HTTP/1.0 200 OK");

		$xmlRawData = (IN_PRODUCTION  and empty($_REQUEST['debug'])) ? $GLOBALS['HTTP_RAW_POST_DATA'] : $this->testXML;

		if(!empty($xmlRawData)):

			$xml = new SimpleXMLElement($xmlRawData);



			$requests = array();

			foreach($xml->message as $message):
				$request = array();
				$request['transaction_id'] = (string) $message->{'transaction-id'};
				$request['requested'] = (string) date::getForDb($message->time);
				$request['keyword'] = (string) $message->keyword;
				$request['provider'] = (string) $message->provider;
				$request['short_number'] = (string) $message->{'short-number'};
				$request['phone'] = (string) $message->phone;
				$request['message'] = (string) $message->text;
				$request['money'] = (string) $message->money;
				$request['regionid'] = (string) $message->{'region-id'};
				$requests[] = $request;
			endforeach;

			return $requests;

		endif; //xmlRawData

	}

	public function sendMessage($data, $sync = FALSE){

		if(!isset($data[0])) $data = array($data);

		$response = array();
		// Устанавливаем соединение

		foreach($data as $i => $item):

			$XML = '<?xml version="1.0"?>
<response>
	<authentication>
		<login>'.self::LOGIN.'</login>
		<password>'.self::PASSWORD .'</password>
	</authentication>
	<message>
		<transaction-id>'.$item['transaction_id'].'</transaction-id>
		<text>'.text::xml_convert($item['text']).'</text>
	</message>
</response>
';

			// Данные HTTP-запроса
			$senddata = "POST " . self::PATH . " HTTP/1.1
Content-type: application/x-www-form-urlencoded
Connection: close
Host: ". self::HOST ."
Content-Length: ". strlen($XML) ."

" . $XML;
			$fp = fsockopen(self::HOST, self::PORT, $errno, $errstr, 10);

			// Проверяем успешность установки соединения
			if($fp):

				// Отправляем HTTP-запрос серверу
				fwrite($fp, $senddata);
                stream_set_timeout($fp, 10);
				// Получаем ответ
				$responsetext = '';

				while (!feof($fp)):
					$responsetext .= fgets($fp, 1024);
				endwhile;

				if(preg_match("/<code>(\d)<\/code>/", $responsetext, $matches)):
					$responseStatus = $matches[1];
				endif;

				if(!empty($responseStatus)):
					$response[$item['id']] = self::$responseStatuses[$responseStatus];
				endif;

				@fclose($fp);
			else:

				if(!count($response)):
					return "Невозможно установить соединение с сервером. $errstr ($errno)";
				else:
					$response[$item['id']] = "Невозможно установить соединение с сервером. $errstr ($errno)";
				endif;

			endif;

		endforeach;


		return $response;

	}


}