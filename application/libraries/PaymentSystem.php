<?php defined('SYSPATH') or die('No direct script access.');

require_once('PaymentSystem/PaymentSystem.interface.php');

class PaymentSystem_Core {

	function getPaymentSystem($payment_system) {

		switch($payment_system):
			case 'EKARMON':
			case 'ekarmon':
				return self::getEkarmonPaymentSystem();
			break;
			case 'test':
				return self::getTestPaymentSystem();
			break;
		endswitch;

		return NULL;
	}

	function getEkarmonPaymentSystem() {
		require_once('PaymentSystem/Ekarmon/EkarmonPaymentSystem.php');
		return new EkarmonPaymentSystem();
	}

    function getTestPaymentSystem() {
		require_once('PaymentSystem/Test/test.php');
		return new TestPaymentSystem();
    }
}

/* ?> */