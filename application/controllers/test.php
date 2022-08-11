<?php defined('SYSPATH') or die('No direct script access.');

class Test_Controller extends Controller_Core {

    private $order;

	public function __construct()
	{
        parent::__construct();
        $this->auto_render = FALSE;




        if (!in_array($_SERVER['REMOTE_ADDR'], array('178.218.201.98', '127.0.0.1')))
        {
            die('The Matrix has you...');
        }
        $this->order = array(
            'id' => '10013',
            'date' => date('Ymd'),
//            'date' => '20130417',
            'amount' => '5',
        );
    }

    public function index()
    {
        echo 'test ok';
    }

    public function info()
    {
        phpinfo();
    }

    public function request_sign()
    {
        try
        {
    //        $merchantID = '106600000000010';
    //        $terminalID = '10660023';
    //        $extOrderID = $this->order['id'];
    //        $transAmount = $this->order['amount'];
    //        $status = '1';
    //        $extOrderInputDate = $this->order['date'];
    //        $occurDateTime = '20130409131406';
    //        $currencyCode = '';

            $testPaymentSystem = PaymentSystem::getPaymentSystem('GatewayTech');
            echo $testPaymentSystem->generateForm($this->order)."<br/>\n";

            $sign = $testPaymentSystem->getSign($this->order);
            echo $sign."<br/>\n";
            $test = array(
                '106600000000010',
                '10660023',
                '295030588621745',
                '20131209',
                '569.94',
                'UZS',
                'ru_RU',
                'http://195.158.31.10:9053/eshop/checkout_process.php',
            );
            $test_data = implode('', $test);
            echo $testPaymentSystem->signRSA($test_data);
            $data = $testPaymentSystem->export_data_to_sign($this->order, TRUE);
            echo (int)$testPaymentSystem->signCheck($data, $sign);
        }
        catch (Exception $e)
        {
            Kohana::log('debug', $e->getMessage().': '.$e->getTraceAsString());
        }
    }

    public function payment_form()
    {
        $testPaymentSystem = PaymentSystem::getPaymentSystem('GatewayTech');
        echo $testPaymentSystem->generateForm($this->order);
    }

    public function request_payment()
    {
        try
        {
            $testPaymentSystem = PaymentSystem::getPaymentSystem('GatewayTech');
            $request_url = $testPaymentSystem->buildPaymentRequestURL($this->order);

    //        echo $request_url;

            url::redirect($request_url);
        }
        catch (Exception $e)
        {
            echo $e->getMessage().': '.$e->getTraceAsString();
        }
    }

    public function handle_payment_response()
    {
//        этот параметр можно задавать только в php.ini
//        или главном кофиге веб-сервера через php_amin_value
//        ini_set('suhosin.get.max_value_length', 1024);
        try
        {
            $testPaymentSystem = PaymentSystem::getPaymentSystem('GatewayTech');

            $testPaymentSystem->verifyResponse();
        }
        catch (Exception $e)
        {
            echo $e->getMessage().': '.$e->getTraceAsString();
        }
    }

    public function request_status()
    {
        try
        {
            $testPaymentSystem = PaymentSystem::getPaymentSystem('GatewayTech');
            $request_url = $testPaymentSystem->buildPaymentStatusURL($this->order);

    //        echo $request_url;

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $request_url,
                CURLOPT_AUTOREFERER => TRUE,
                CURLOPT_FOLLOWLOCATION => TRUE,
                CURLOPT_HEADER => FALSE,
                CURLOPT_RETURNTRANSFER => TRUE,
            ));

            $response = curl_exec($ch);

            curl_close($ch);

            $testPaymentSystem->verifyStatusResponse($response);
        }
        catch (Exception $e)
        {
            echo $e->getMessage().': '.$e->getTraceAsString();
        }
    }

    public function paynet($cmd = '')
    {
        $allowed_cmds = array(
            'performtransaction',
            'getstatement',
            'getinformation',
            'checktransaction',
            'canceltransaction',
        );
        if (empty($cmd))
        {
            echo '<html><body>'
            .'<p>PerformTransaction(проведение платежа)</p>'
            .'<form action="/test/paynet/performtransaction" method="POST">'
            .'<table>'
            .'<tr>'
            .'<td>Service ID</td>'
            .'<td><input type="text" name="service_id" value="1"/></td>'
            .'</tr>'
            .'<tr>'
            .'<td>Customer ID</td>'
            .'<td><input type="text" name="customer_id" value=""/></td>'
            .'</tr>'
            .'<tr>'
            .'<td>Bonus Count</td>'
            .'<td><input type="text" name="bonus_cnt" value=""/></td>'
            .'</tr>'
            .'<td>Amount</td>'
            .'<td><input type="text" name="amount" value=""/></td>'
            .'</tr>'
            .'<tr><td colspan="2"><input type="submit" value="buy"></td></tr>'
            .'</table>'
            .'</form>'

            .'<hr/>'

            .'<p>GetStatement(история платежей)</p>'
            .'<form action="/test/paynet/getstatement">'
            .'<table>'
            .'<tr>'
            .'<td>Service ID</td>'
            .'<td><input type="text" name="service_id" value="1"/></td>'
            .'</tr>'
            .'<tr>'
            .'<td>Date from</td>'
            .'<td><input type="text" name="datefrom" value=""/></td>'
            .'</tr>'
            .'<tr>'
            .'<td>Date to</td>'
            .'<td><input type="text" name="dateto" value=""/></td>'
            .'</tr>'
            .'<tr><td colspan="2"><input type="submit" value="get"></td></tr>'
            .'</table>'
            .'</form>'

            .'<hr>'

            .'<p>GetInformation(информация о балансе клиента)</p>'
            .'<form action="/test/paynet/getinformation">'
            .'<table>'
            .'<tr>'
            .'<td>Service ID</td>'
            .'<td><input type="text" name="service_id" value="1"/></td>'
            .'</tr>'
            .'<tr>'
            .'<td>Customer ID</td>'
            .'<td><input type="text" name="customer_id" value=""/></td>'
            .'</tr>'
            .'<tr><td colspan="2"><input type="submit" value="get"></td></tr>'
            .'</table>'
            .'</form>'

            .'<hr>'

            .'<p>CheckTransaction(информация о состоянии платежа)</p>'
            .'<form action="/test/paynet/checktransaction">'
            .'<table>'
            .'<tr>'
            .'<td>Service ID</td>'
            .'<td><input type="text" name="service_id" value="1"/></td>'
            .'</tr>'
            .'<tr>'
            .'<td>Transaction ID</td>'
            .'<td><input type="text" name="transaction_id" value=""/></td>'
            .'</tr>'
            .'<tr><td colspan="2"><input type="submit" value="check"></td></tr>'
            .'</table>'
            .'</form>'

            .'<hr>'

            .'<p>CancelTransaction(отмена платежа)</p>'
            .'<form action="/test/paynet/canceltransaction">'
            .'<table>'
            .'<tr>'
            .'<td>Service ID</td>'
            .'<td><input type="text" name="service_id" value="1"/></td>'
            .'</tr>'
            .'<tr>'
            .'<td>Transaction ID</td>'
            .'<td><input type="text" name="transaction_id" value=""/></td>'
            .'</tr>'
            .'<tr><td colspan="2"><input type="button" value="cancel"></td></tr>'
            .'</table>'
            .'</form>'

            .'</body></html>'
            ;
        }
        else
        {
            try
            {
//                $wsdl = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')?'https://':'http://').$_SERVER['SERVER_NAME'].'/secure/soap_data/ProviderWebService.wsdl';
                $wsdl = 'https://zor.uz/secure/soap_data/ProviderWebService.wsdl';
                $paynet_config = Kohana::config('payment.method.paynet');
                ini_set("soap.wsdl_cache_enabled", 0); // отключаем кэширование WSDL
                ini_set("soap.wsdl_cache", WSDL_CACHE_NONE); // отключаем кэширование WSDL
                $options = array(
                    'soap_version' => SOAP_1_2,
                    'cache_wsdl' => WSDL_CACHE_NONE,
//                    'username' => $paynet_config['username'],
//                    'password' => $paynet_config['password'],
                );
                $soap_client = new SoapClient($wsdl, $options);
                switch ($cmd)
                {
                    case 'performtransaction':
                        $params = array(
                            'username' => $paynet_config['username'],
                            'password' => $paynet_config['password'],
                            'serviceId' => $_POST['service_id'],
                            'amount' => $_POST['amount'],
                            'parameters' => array(
                                array(
                                    'paramKey' => 'customer_id',
                                    'paramValue' => $_POST['customer_id'],
                                ),
                                array(
                                    'paramKey' => 'bonus_cnt',
                                    'paramValue' => $_POST['bonus_cnt'],
                                ),
                            ),
                            'transactionId' => (int)round(microtime(TRUE)*10),
                            'transactionTime' => date('Y-m-d\TH:i:s'),
                        );
//                        die(print_r($params, TRUE));
                        $transaction_result = $soap_client->PerformTransaction($params);
                        echo '<pre>'.print_r($transaction_result, TRUE).'</pre>';
                    break;
                    case 'getstatement':
                        $params = array(
                            'username' => $paynet_config['username'],
                            'password' => $paynet_config['password'],
                            'serviceId' => $_GET['service_id'],
                            'dateFrom' => $_GET['datefrom'],
                            'dateTo' => $_GET['dateto'],
                        );
                        $statement_info = $soap_client->GetStatement($params);
                        echo '<pre>'.print_r($statement_info, TRUE).'</pre>';
                    break;
                    case 'getinformation':
                        $params = array(
                            'username' => $paynet_config['username'],
                            'password' => $paynet_config['password'],
                            'serviceId' => $_GET['service_id'],
                            'parameters' => array(
                                array(
                                    'paramKey' => 'customer_id',
                                    'paramValue' => $_GET['customer_id'],
                                ),
                            )
                        );
                        $transactions_history = $soap_client->GetInformation($params);
                        echo '<pre>'.print_r($transactions_history, TRUE).'</pre>';
                    break;
                    case 'checktransaction':
                        $params = array(
                            'username' => $paynet_config['username'],
                            'password' => $paynet_config['password'],
                            'serviceId' => $_GET['service_id'],
                            'transactionId' => $_GET['transaction_id'],
                        );
                        $transaction_info = $soap_client->CheckTransaction($params);
                        echo '<pre>'.print_r($transaction_info, TRUE).'</pre>';
                    break;
                    case 'canceltransaction':
                    break;
                    default:
                        $func_list = $soap_client->__getFunctions();
                        echo '<pre>'.print_r($func_list, TRUE).'</pre>';
                }
                echo '<br/><a href="/test/paynet/">test again</a>';
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }

    public function testssl()
    {
        $urls = array(
            "https://cas.ucdavis.edu/login",
            "https://server.db.kvk.nl/",
            "https://gmail.com/"
        );

        foreach ($urls as $url)
        {
            try
            {
                $fp = fopen($url, 'r');
                print "$url - ";
                if ($fp === FALSE)
                {
                    print "FAIL\n";
                }
                else
                {
                    $data = stream_get_contents($fp);
                    print "OK ". strlen($data)." bytes\n";
                }
                fclose($fp);
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }
}
?>
