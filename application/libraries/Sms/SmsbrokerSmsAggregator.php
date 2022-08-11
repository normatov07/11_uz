<?php
require_once(dirname(__FILE__) . '/SmsAggregator.interface.php');

//class SmsRequest{}

class SmsbrokerSmsAggregator implements SmsAggregator {

	const LOGIN = 'offerman';
	const PASSWORD = 'k9mYUqRB';
	
	const HOST = 'transport.smspartner.ru';
	const PORT = '80';
	const PATH = '';

	static $responseStatuses = array(

		'1' => 'Message Length is invalid',
		'2' => 'Command Length is invalid',
		'3' => 'Invalid Command ID',
		'8' => 'System Error',
		'10' => 'Invalid Source Address',
		'11' => 'Invalid Dest Addr',
		'14' => 'Message Queue Full',
		'1024' => 'No TLV',
		'1025' => 'Bad tariff',
		'1026' => 'No Transaction ID',
		'1027' => 'Low balance',
		'1028' => 'Billed, not delivered',
		'1029' => 'Purchase with time restriction',
		'1030' => 'Amount is out of range',
		'1031' => 'Spam alert',
		'1032' => 'SMSC error',
		'1033' => 'Network error',
		'1034' => 'Billing platform error',
		'1035' => 'No more message to sent',
		'1036' => 'Wrong Transaction ID',
		'1040' => 'MT_QUOTA exceeded - превышение количества исходящих sms-сообщений',
		'1041' => 'MAX_MT_MO_RATIO exceeded - превышение соотношения входящих и исходящих сообщений',
		'1042' => 'Custom Reception forbidden – запрещена отправка исходящего sms-сообщения произвольному получателю',
		'1043' => 'MAX_MT_IN_SMS_RESPONSE exceeded - превышение количества sms-сообщений в одном sms-response',
		'1044' => 'MAX_MT_IN_POLL_RESPONSE exceeded - превышение количества sms-сообщений в одном poll-response',
		'1045' => 'VALIDITY_PERIOD exceeded - превышение времени ожидания отправки sms-сообщения',
			
	);

	private $testXML = '<?xml version="1.0" encoding="UTF-8"?>
<sms-request version="1.0">
     <message id="54591" submit-date="2008-10-13 13:30:10" msisdn="+79991234567" service-number="2893" operator="MTS" operator-id="100" keyword="70+15" message-count="1">
          <content type="text/plain">70+15+400</content>
     </message>
</sms-request>';


	function __construct() {
		if(IN_PRODUCTION and empty($_REQUEST['debug'])) error_reporting(0);
	}
		
	public function getRequest(){
				
		header("HTTP/1.0 200 OK");
		
		$xmlRawData = (IN_PRODUCTION  and empty($_REQUEST['debug'])) ? $GLOBALS['HTTP_RAW_POST_DATA'] : $this->testXML;
	
		if(!empty($xmlRawData)):
			
			$xml = new SimpleXMLElement($xmlRawData);		
									
			$requests = array();			
			
			foreach($xml->message as $message)
			{
				
				$request = array();
				
				foreach($message->attributes() as $name => $value)
				{
					switch($name)
					{
						case 'id':
							$request['transaction_id'] = (string) $value;
						break;
						case 'submit-date':
							$request['requested'] = (string) date::getForDb($value);
						break;
						case 'keyword':
							$request['keyword'] = (string) $value;
						break;
						case 'operator':
							$request['provider'] = (string) $value;
						break;
						case 'service-number':
							$request['short_number'] = (string) $value;
						break;
						case 'msisdn':
							$request['phone'] = (string) $value;
						break;			 
							
					}				
				};
				
				$request['money'] = '';
				$request['regionid'] = '';
				$request['message'] = (string) $message->content;
				$requests[] = $request;
			};			
				
			return $requests;	
					
		endif; //xmlRawData
	
	}
	
	public function sendMessage($data, $sync = FALSE){
	
		if(!isset($data[0])) $data = array($data);
		
		$response = array();
		// Устанавливаем соединение		
		$XML = '<?xml version="1.0" encoding="UTF-8"?>
<sms-response';
		
		if(empty($sync))
		{
			$XML .= ' login="'.self::LOGIN.'" password="'.self::PASSWORD.'"';
		
		};
		
		$XML .= ' delivery-notification-requested="false" version="1.0">';
		
		foreach($data as $i => $item):		
	
			$XML .= '
      <message id="'.($i+1).'" ref-id="'.$item['transaction_id'].'" msisdn="'.$item['phone'].'" service-number="'.$item['short_number'].'" operator="'.$item['operator'].'">
            <content type="text/plain">'.text::xml_convert($item['text']).'</content>
      </message>';
			
		endforeach;			
			
		
		$XML .= '      
</sms-response>';
		
		if(!empty($sync))
		{
			echo $XML;
			$response = 'Отправлен синхронный ответ.';	
		}
		else
		{
		
			// Данные HTTP-запроса 
			$senddata = "POST " . self::PATH . " HTTP/1.1
Content-type: \"text/xml; charset=UTF-8\"
Host: ". self::HOST ."
Content-Length: ". strlen($XML) ."

" . $XML;			
			$fp = fsockopen(self::HOST, self::PORT, $errno, $errstr, 30);
			
			// Проверяем успешность установки соединения 
			if($fp): 
			
				// Отправляем HTTP-запрос серверу 
				fwrite($fp, $senddata); 
				
				// Получаем ответ 
				$responsetext = '';

				while (!feof($fp)):
					$string = fgets($fp, 1024); 
					if(!empty($responsetext) || preg_match('/</',$string)) $responsetext .= $string; 
				endwhile;

				/*if(preg_match("/<code>(\d)<\/code>/", $responsetext, $matches)):
					$responseStatus = $matches[1];
				endif;
	
				if(!empty($responseStatus)):
					$response[$item['id']] = self::$responseStatuses[$responseStatus];
				endif;				
				*/
				
				$xml = new SimpleXMLElement($responsetext);		
									
				foreach($xml->status as $status)
				{
					$code = '';
					$item_id = '';
					foreach($status->attributes() as $name => $value)
					{
						switch($name)
						{
							case 'ordinal':
								$item_id = $value;
							break;
							case 'code':
								$code = $value;
							break;
						}	
					};
					if(!empty($code)) $response[$item_id] = self::$responseStatuses[$code];
				};
				
				@fclose($fp);
			else:
				
				if(!count($response)):
					return "Невозможно установить соединение с сервером. $errstr ($errno)";
				else:
					$response = "Невозможно установить соединение с сервером. $errstr ($errno)";
				endif;
			
			endif;
		
			return $response;
		}
	}


}