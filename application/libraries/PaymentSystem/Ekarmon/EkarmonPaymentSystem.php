<?php

require_once(dirname(__FILE__) . '/../PaymentSystem.interface.php');

class EkarmonPaymentSystem implements Payment {

    const VERSION = 1;

    function __construct() {
        // this file is public key of eKarmon and used for checking authentity of variables received from eKarmon.
		$this->KEYS_DIR = 'file://'. dirname(__FILE__) .'/';
		
        $this->ek_key = $this->KEYS_DIR . 'eKarmon.pem';
        // This file is the .pem  file which contains public and private keys of the store.
        // It is used to sign on sending variables to eKarmon.
		$this->psData = Lib::config('payment.method', 'ekarmon');
		
        $this->shop_key = $this->KEYS_DIR . $this->psData['keyFileName'].'.pem';
    }

    function getSign($order) {
        $signature = NULL;

        $orderTotal = $order['description'] . $order['price'];

        $private_key = openssl_pkey_get_private($this->shop_key, $this->psData['keyPassword']);

        if (openssl_sign($orderTotal, $signature, $private_key)) {
            return bin2hex($signature);
        }

        return NULL;
    }

    function generateForm($order) {

        $params = array(
            'version' => self::VERSION,
            'shopId' => $this->psData['shopID'],
            'orderId' => $order['id'],
            'order' => $order['description'],
            'orderDescription' => $order['orderDescription'],
            'sum' => $order['price'],
            'sign' => $this->getSign($order),
            'returnURL' =>  $order['return_url']
        );

        $form = '<form method="post" name="payment_form" action="' . $this->psData['paymentURL'] . '">';
        foreach($params as $key => $item) {
            $form .= '<input type="hidden" name="'.$key.'" value="'.$item.'">'."\n";
        }
        $form .= '<input type="submit" class="submit" name="pay" value="Перейти к оплате">
		
		</form>';

        return $form;
    }

    function getResponse() {
        return array(
            'order_id' => @$_POST['orderId'],
            'ps_transaction_id' => @$_POST['transactionId']
        );
    }

    function verifyResponse() {
        $orderId = @$_POST['orderId']; // Номер заказа магазина
        $transactionId = @$_POST['transactionId']; // Номер транзакции системы еКармон
        $eKSign = @$_POST['eKSign']; // ЭЦП системы на orderId и transationId

        $orderTotal = $orderId . $transactionId;
        $pub_key = openssl_pkey_get_public($this->ek_key);
		$iscorrect = openssl_verify($orderTotal, $this->hex2bin($eKSign), $pub_key);
		if(!$iscorrect):
			@Lib::log("Ошибка подтверждения платежа через eKarmon: Неверные данные:
			orderId = ". @$_POST['orderId'] . "// Номер заказа магазина
			transactionId = " . @$_POST['transactionId'] . "// Номер транзакции системы еКармон
			eKSign = " . @$_POST['eKSign'] .'!');
		endif;
        return $iscorrect;
    }

    private function hex2bin($data) {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }
}

