<?php defined('SYSPATH') or die('No direct script access.');
/**
 * интерфейс для платежной системы Gateway Technologies
 */

require_once(dirname(__FILE__) . '/../PaymentSystem.interface.php');

class GatewayTechPaymentSystem implements Payment {
    /**
     * merchant ID сервиса
     * @var int
     */
    protected $merchant_id;
    /**
     * terminal ID сервиса
     * @var int
     */
    protected $terminal_id;
    /**
     * код валюты (UZS)
     * @var string
     */
    public $currency_code = 'UZS';
    /**
     * язык локализации страницы платежа (ru_RU, en_US, uz_UZ)
     * @var string
     */
    public $locale = 'ru_RU';

    protected $callback_url;

    /**
     *
     * сертификат платежной системы (полный путь в файловой системе)
     * содержит публичный ключ, который используется для проверки подписи
     * ответов от платежной системы о состоянии платежа
     *
     * @var string
     */
    protected $gate_cert_file;
    /**
     * сертификат постващика услуг (полный путь в файловой системе)
     * содержит публичный ключ, который может быть использован для проверки подписей,
     * сделанных приватным ключом поставщика(для тестирования)
     *
     * @var string
     */
    protected $supplier_cert_file;
    /**
     * приватный ключ поставщика услуг (полный путь в файловой системе)
     * используется для подписи запросов к платежной системе
     *
     * @var string
     */
    protected $supplier_key_file;

    protected $payment_gate_url;

    function __construct()
    {
//        if (IN_PRODUCTION)
//        {
//            $this->callback_url = 'http://zor.uz/bonus/handle_payment_response/';
//
//        }
//        else
        {
            $this->callback_url = 'http://zor.uz/test/handle_payment_response/';
            $this->payment_gate_url = 'http://195.158.31.10:9052/gate';

            $this->merchant_id = '106600000000010';
            $this->terminal_id = '10660023';

            $this->supplier_cert_file = ''.__DIR__.'/supplier.cert.pem';
            $this->supplier_key_file = ''.__DIR__.'/supplier.rsa.pem';

            $this->gate_cert_file = ''.__DIR__.'/gate.cert.pem';
//            $this->gate_cert_file = $this->supplier_cert_file;
        }
    }

    /**
     * генерирует подпись для запроса на оплату
     *
     * @param int $order_id - ID заказа
     * @param string $order_date - дата заказа
     * @param float $amount - сумма на оплату
     * @param string $callback_url - адрес страницы, на которую будет сделан редирект после проведения оплаты
     * @return string
     */
    function getSign($order)
    {
        $sign = '';
        try
        {
            $params = array(
                'merchantID' => $this->merchant_id,
                'terminalID' => $this->terminal_id,
                'extOrderID' => $order['id'],
                'extOrderInputDate' => $order['date'],
                'transAmount' => $order['amount'],
                'currencyCode' => $this->currency_code,
                'locale' => $this->locale,
                'callbackURL' => $this->callback_url,
            );
            $data = implode('', $params);

//            Kohana::log('error', $data);

            $sign = $this->signRSA($data);
        }
        catch (Exception $e)
        {
            Kohana::log('error', $e->getMessage().': '.$e->getTraceAsString());
        }

//        $sign = strtoupper($sign);
        return $sign;
    }

    function generateForm($order){

        $params = array();

        $form = View::factory('my/gatewaytech_payment');
        $form->set('params', $params);

        return $form->render();
    }

    function buildPaymentRequestURL($order)
    {
        $params = array(
            'merchantID' => $this->merchant_id,
            'terminalID' => $this->terminal_id,
            'extOrderID' => $order['id'],
            'extOrderInputDate' => $order['date'],
            'transAmount' => $order['amount'],
            'currencyCode' => $this->currency_code,
            'locale' => $this->locale,
            'callbackURL' => $this->callback_url,
        );

        $data = implode('', $params);

        $sign = $this->signRSA($data);

        $request_url = $this->payment_gate_url
                .'/Transaction/?'
                .http_build_query($params)
                .'&sign='.$sign
            ;

        return $request_url;
    }

    function buildPaymentStatusURL($order)
    {
        $params = array(
            'merchantID' => $this->merchant_id,
            'terminalID' => $this->terminal_id,
            'extOrderID' => $order['id'],
            'extOrderInputDate' => $order['date'],
            'locale' => $this->locale,
        );

        $data = implode('', $params);

        $sign = $this->signRSA($data);

        $status_url = $this->payment_gate_url
                .'/Transaction/orderInquiry?'
                .http_build_query($params)
                .'&sign='.$sign
            ;

        return $status_url;
    }

    function getResponse($order_data){}

    /**
     * проверяет подлинность данных в ответе от платежной системы,
     * а также статус платежа - прошел или нет
     *
     * @uses $_GET['result'] - данные в формате XML
     *
     */
    function verifyResponse()
    {
        try
        {
            $status = 0;
//            print_r($_REQUEST);
            $response = $_GET['result'];
/*
 *             $response = '<?xml version="1.0" encoding="UTF-8"?><objects><OrderPayInfo><merchantID>106600000000010</merchantID><terminalID>10660023</terminalID><extOrderID>400000051</extOrderID><transAmount>48</transAmount><status>1</status><extOrderInputDate>20130417</extOrderInputDate><occurDateTime>20130418105939</occurDateTime><currencyCode>UZS</currencyCode><errorMsg></errorMsg><sign>240453556CA177F3DCBDA6314E57284E6A8CDB9F8C9FE3892262DEE1CB114CAD1C3731D7E977191E9FC8F2EA25847C59679C958537CB8D183509D0BC19E9FAAD090E770BCE3760D3DB45ABA754871FD03F630C62A9976EB4D8790B0A6E8392BB874D87C84A91F2CD6C7B329A691FF16A244347ED4258DE617DA4B0067CCEA64A</sign><sign>9A22EF18B6E1A26F3A0AB25AC5919739167EA604E3986F8A3273B56143BEA6270805C95524ABC3C0CC8B411B1F48627F0354B1F9FF6C14488ADC87179D0E1A36A6AD2251876962E13213A268C52E573760DE5287402230C42C981FE7EDC0EC997CE1868F0E37EA02E7D3274205B1A07A07EBF3AC7FC122C27789698A2900C465</sign></OrderPayInfo></objects>';
 */
            $xml = new SimpleXMLElement($response, $options = 0);
            Kohana::log('debug', __METHOD__.': '.print_r($xml, TRUE));

            // генерируем подпись для проверки ответа на запрос по оплате
            $params = array(
                $xml->merchantID,
                $xml->terminalID,
                $xml->extOrderID,
                $xml->transAmount,
                $xml->status,
                $xml->extOrderInputDate,
                $xml->occurDateTime,
                $xml->currencyCode,
            );
            Kohana::log('debug', __METHOD__.': '.print_r($params, TRUE));

            $data = implode('', $params);

            // если сгенерированная подпись совпадает с той что получена в ответе
            if ($this->signCheck($data, $xml->sign[1]))
            {
                if ($xml->status == 1)
                {
                    // платеж прошел успешно
                    Kohana::log('success', __METHOD__.': payment: success');
                    $status = 1;
                }
                else
                {
                    // платеж не прошел
                    throw new Exception('payment: failure');
                    $status = 0;
                }
            }
            else
            {
                // иначе ответ невалидный, возможно подделка
                throw new Exception('sign check failure');
                $status = -1;
            }
            return $status;
        }
        catch (Exception $e)
        {
            Kohana::log('error', $e->getMessage().': '.$e->getTraceAsString());
            return $status;
        }
    }

    public function verifyStatusResponse($response)
    {
        try
        {
            $status = 0;
            $xml = new SimpleXMLElement($response, $options = 0);

            Kohana::log('debug', __METHOD__.': '.print_r($xml, TRUE));
            // генерируем подпись для проверки ответа на запрос по оплате
            $params = array(
                $xml->OrderPayInfo->merchantID,
                $xml->OrderPayInfo->terminalID,
                $xml->OrderPayInfo->extOrderID,
                $xml->OrderPayInfo->transAmount,
                $xml->OrderPayInfo->status,
                $xml->OrderPayInfo->extOrderInputDate,
                $xml->OrderPayInfo->occurDateTime,
                $xml->OrderPayInfo->currencyCode,
            );
            Kohana::log('debug', __METHOD__.': '.print_r($params, TRUE));

            $data = implode('', $params);

            // если сгенерированная подпись совпадает с той что получена в ответе
            if ($this->signCheck($data, $xml->OrderPayInfo->sign[1]))
            {
                if ($xml->OrderPayInfo->status == 1)
                {
                    // платеж прошел успешно
//                    throw new Exception("payment status: success");
                    Kohana::log('success', __METHOD__.': payment status: success');
                    $status = 1;
                }
                else
                {
                    // платеж не прошел
                    throw new Exception("payment status: failure");
                    $status = 0;
                }
            }
            else
            {
                // иначе ответ невалидный, возможно подделка
                throw new Exception("sign check failure");
                $status = -1;
            }
            return $status;
        }
        catch (Exception $e)
        {
            Kohana::log('error', $e->getMessage().': '.$e->getTraceAsString());
            return $status;
        }
    }

    /**
     * сертификаты работают так:
     * есть приватный ключ, сертификат и публичный ключ, который может быть получен из сертификата
     * запросы подписываются приватным ключом и проверяются публичным ключом
     *
     * в нашем случае есть 2 стороны и по 2 приватных ключа, 2 сертификата и 2 публичных ключа:
     * один у нас, как поставщика услуг, второй у платежной системы
     * мы свои запросы подписываем нашим приватным ключом, а
     * платежная система эти запросы проверяет нашим публичным ключом
     * (соответсвенно, у платежной системы должен быть этот ключ)
     * платежная система подписывает ответы на запрос своим приватным ключом,
     * а мы проверяем эту подпись публичным ключом платежной системы
     * (у нас должен иметься публичный ключ платежной системы)
     *
     * для тестов нам предоставили приватный ключ и сетификат для поставщика услуг,
     * и публичный ключ платежной системы
     *
     * приватный ключ и сертификат в принципе мы можем сгенерировать сами
     * (платежке нужен только публичный сертификат),
     * но тут вопрос контроля со стороны платежной системы
     */


    /**
     * генерирует подпись для указанных данных, с использованием нашего приватного ключа,
     * полученного от платежной системы
     *
     * @param string $data
     * @return string
     */
    public function signRSA($data)
    {
        // Binary signature
        $signature = "";
        // fetch private key from file and ready it
        $fp = fopen($this->supplier_key_file, 'r');
        $priv_key = fread($fp, 8192);
        fclose($fp);

        $pkeyid = openssl_pkey_get_private($priv_key);

        // compute signature
        openssl_sign($data, $signature, $pkeyid, OPENSSL_ALGO_SHA1);

        // free the key from memory
        openssl_free_key($pkeyid);

        // Encode to HEX format
        $signature_final = bin2hex($signature);

        return $signature_final;
    }

    /**
     * проверяет подпись для указанных данных, сделанную нами с помощью публичного ключа
     * платежной системы, сравнивая ее с подписью полученной от платежной системы
     *
     * @param string $data
     * @param string $signature
     * @return boolean
     * @throws Exception
     */
    public function signCheck($data, $signature)
    {
        //Decode from HEX format
        $signatureBinary = pack('H*',$signature);

        // fetch public key from certificate and ready it
        $fp = fopen($this->gate_cert_file, 'r');
        $cert = fread($fp, 8192);
        fclose($fp);

        $pubkeyid = openssl_pkey_get_public($cert);

        // Check signature
        $ok = openssl_verify($data, $signatureBinary, $pubkeyid, OPENSSL_ALGO_SHA1);

        if ($ok > 0)
        {
            return true;
        }
        else
        if ($ok == 0)
        {
            return false;
        }
        else
        {
           throw new Exception(openssl_error_string());
        }
    }

    public function export_data_to_sign($order_data, $as_string = FALSE)
    {
        $data = array(
            $this->merchant_id,
            $this->terminal_id,
            $order_data['id'],
            $order_data['date'],
            $order_data['amount'],
            $this->currency_code,
            $this->locale,
            $this->callback_url,
        );

        if ($as_string) $data = implode($data);

        return $data;
    }
}