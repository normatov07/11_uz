<?php defined('SYSPATH') or die('No direct script access.');

//Новая версия 04.12.2015 @maxmudov
class Paynet_Controller extends Controller_Core {

	public function __construct()
	{
		parent::__construct();
		$this->auto_render = false;
	}

	public function index()
	{

        file_put_contents('loger/paynet.log', PHP_EOL.json_encode($_SERVER).PHP_EOL.PHP_EOL, FILE_APPEND);

		error_log(print_r($_SERVER, true)."\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');
		error_log("[".$_SERVER['REMOTE_ADDR']."](".date('Y-m-d H:i:s')."):\n".file_get_contents('php://input')."\n\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');

		try {

			$allowed_ip_mask = Lib::config('payment.method', 'paynet', 'allowed_ip_mask');

			$st = FALSE;

			$i = 0;
			while(($i < count($allowed_ip_mask)) && (!$st))
			{
				$st = $this->check_ip_range($_SERVER['REMOTE_ADDR'], $allowed_ip_mask[$i]);
				$i++;
			}

			error_log((int)$st."(access allowed)\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');

			if (!$st) {
				Kohana::log('error', Kohana::lang('paynet.attemp_to_access_from_ip_other_than_permitted').' '.$_SERVER['REMOTE_ADDR']);
				throw new SoapFault('Server', 'Forbidden');
			}

			ini_set("soap.wsdl_cache_enabled", 0); // отключаем кэширование WSDL
			//ini_set("soap.wsdl_cache_ttl", 0); // отключаем кэширование WSDL
			ini_set("soap.wsdl_cache", WSDL_CACHE_NONE); // отключаем кэширование WSDL

			$context_options = array(
				'ssl' => array(
                    'ciphers'=>'SSLv3',
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'verify_depth' => 0,
                    'allow_self_signed' => true
				),
			);
			$options = array(
				'cache_wsdl' => WSDL_CACHE_NONE,
				'context' => stream_context_create($context_options),
			);

            //$wsdl_path = "https://app.uz/public/soap_data/ProviderWebService.wsdl";
            $wsdl_path = "/var/www/zor.uz/secure/soap_data/ProviderWebService.wsdl";
			$server = new SoapServer($wsdl_path, $options);


			error_log("setting class\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');
			$server->setClass("Paynet");
			$server->setPersistence(SOAP_PERSISTENCE_SESSION);

			error_log("starting handle\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');
			error_log("ip: ".$_SERVER['REMOTE_ADDR']."\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');

			$server->handle();

			error_log("ending handle\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');
			error_log("[".$_SERVER['REMOTE_ADDR']."](".date('Y-m-d H:i:s').") succeeded\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');


		}
		catch (Exception $e)
		{
			Kohana::log('error', $e->getMessage());
			error_log("[".$_SERVER['REMOTE_ADDR']."](".date('Y-m-d H:i:s').") failed\n", 3, $_SERVER['DOCUMENT_ROOT'].'/test.log');
			throw new SoapFault('Server', $e->getMessage());
		}

	}

	protected function check_ip_range($ip, $mask)
	{
		error_log($ip."\n", 3,  $_SERVER['DOCUMENT_ROOT'].'/test.log');

		$ip = ip2long($ip);

		$mask_params = explode('/', $mask);

		$start_ip = ip2long($mask_params[0]) & (pow(2, 32) - pow(2, 32 - $mask_params[1]));
		$start_addr = $start_ip;

		$end_addr = ip2long(long2ip($start_ip+pow(2, 32-$mask_params[1])-1));

//		error_log($start_addr." <= ".$ip." <= ".$end_addr."\n", 3,  $_SERVER['DOCUMENT_ROOT'].'/test.log');

		if (($ip < $start_addr) || ($ip > $end_addr))
		{
			return false;
		}
		return true;
	}
}