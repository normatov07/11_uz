<?php defined('SYSPATH') or die('No direct script access.');
//ini_set('display_errors', false);


//Новая версия 04.12.2015 @maxmudov
class Paynet
{

	public function PerformTransaction($params)
	{
		try
		{
			$timestamp = $this->format_timestamp();

			if (!$this->check_login($params->username, $params->password))
			{

				return array(
					'status' => 601,
					'errorMsg' => 'forbidden',
					'timeStamp' => $timestamp,
					'providerTrnId' => -1
				);
			}


			$data = array();
			if (is_array($params->parameters)) {

				for($i = 0; $i < count($params->parameters);$i++)
				{
					$data[$params->parameters[$i]->paramKey] = $params->parameters[$i]->paramValue;
				}
			}
			else
			{
				$data[$params->parameters->paramKey] = $params->parameters->paramValue;
			}

			if ($data['customer_id'] != strVal(intVal($data['customer_id'], 10)))
			{
				return array(
					'status' => 401,
					'errorMsg' => 'parameter validation error',
					'timeStamp' => $timestamp,
					'providerTrnId' => -1
				);
			}

			if ($data['bonus_cnt'] != strVal(intVal($data['bonus_cnt'], 10)))
			{
				return array(
					'status' => 402,
					'errorMsg' => 'parameter validation error',
					'timeStamp' => $timestamp,
					'providerTrnId' => -1
				);
			}

			$payment = ORM::factory('payment')
				->where('ps_transaction_id=', $params->transactionId)
				->where('user_id=', $data['customer_id'])
				->find();

			if ($payment->loaded)
			{
				return array(
					'status' => 201,
					'errorMsg' => 'transaction already exists',
					'timeStamp' => $timestamp,
					'providerTrnId' => -1
				);
			}


			if ((!isset($data['bonus_cnt']) || empty($data['bonus_cnt']))
				&& (!isset($data['customer_id']) || empty($data['customer_id'])))
			{
				return array(
					'status' => 411,
					'errorMsg' => 'required parameters not specified',
					'timeStamp' => $timestamp,
					'providerTrnId' => -1
				);
			}

			$service = Lib::config('payment.service', 'bonus');
			$tariff = ORM::factory('tariff')->where('service=', 'bonus')->where('method=', 'paynet')->find();

			$user = ORM::factory('user')->find($data['customer_id']);
			if ($user->loaded)
			{

				if (($params->amount/100) != (intVal(2000.00)*$data['bonus_cnt']))
				{
//					$balance = 0;
//					if ($user->account->loaded)
//					{
//						$balance = $user->account->bonuses;
//					}
					$result = array(
						'status' => 413,
						'errorMsg' => 'invalid payment sum for specified bonuses amount',
						'timeStamp' => $timestamp,
						'providerTrnId' => -1
					);
//					$data['bonus_cnt'] = floor($params->amount/($tariff->price*100));
				}
				else
				{
					$balance = 0;
					$account = $user->account;
					$account->total_bonuses = $account->total_bonuses+$data['bonus_cnt'];
					$account->bonuses = $account->bonuses+$data['bonus_cnt'];
					$account->setUpdated();
					if (!$account->user_id)
					{
						$account->user_id = $user->id;
					}
					$account->save();

					$payment = ORM::factory('payment');
					$payment->user_id = $data['customer_id'];
					$payment->added = date('Y-m-d H:i:s');
					$payment->updated = date('Y-m-d H:i:s');
					$payment->service = 'bonus';
					$payment->method = 'paynet';
					$payment->price = $tariff->price*$data['bonus_cnt'];
					$payment->currency = 'uzs';
					$payment->final_price = $payment->price;
					$payment->final_currency = 'uzs';
					$payment->details = 'Покупка ' . format::declension_numerals($data['bonus_cnt'], 'бонуса','бонусов','бонусов') . '. Оплата через Paynet. Транзакция №'.$params->transactionId;
					$payment->units_bought = $data['bonus_cnt'];
					$payment->ps_transaction_id = $params->transactionId;
					$payment->status = 'complete';
					$payment->completed = date('Y-m-d H:i:s');
					$payment->save();

					$balance = $account->bonuses;

					$result = array(
						'status' => 0,
						'errorMsg' => 'ok',
						'timeStamp' => $timestamp,
//						'parameters' => array(
//							array('paramKey'=>'customer_id', 'paramValue'=>$user->id),
//							array('paramKey'=>'balance', 'paramValue'=>$balance)
//						),
						'providerTrnId' => $payment->id,
					);

					$GLOBALS['email'] = $user->email;
					$GLOBALS['bonus_cnt'] = $data['bonus_cnt'];
					$GLOBALS['total'] = $account->bonuses;

					$units = array_slice(Lib::config('payment','unit', 'bonus'), 1);
					$units = array_merge($units, array($units[count($units)-1]));
					$message_data = array(
						'email' => $GLOBALS['email'],
						'bonuses' => $GLOBALS['bonus_cnt'],
						'payment_details' => /*$service['title'] . '. '
								.*/ Lib::config('payment.service', 'bonus', 'details') . ' '
							. format::declension_numerals($GLOBALS['bonus_cnt'], $units)
							. '. '
							. 'Оплата через '. Lib::config('payment.method', 'paynet', 'title') .'.',
						'total' => $GLOBALS['total'],
					);

					$message_tpl = 'adm/user/email/bonus_added_view';
					$subject = 'Вам добавлены бонусы!';

					//Lib::sendEmail($subject, $message_tpl, $message_data);
				}
			}
			else
			{
				$result = array(
					'status' => 302,
					'errorMsg' => 'user does not exists',
					'timeStamp' => $timestamp,
					'providerTrnId' => -1
				);
			}
			return $result;

		}
		catch (Exception $e)
		{
			Kohana::log('error', $e->getMessage());
//			throw new SoapFault('Server', $e->getMessage());
			$result = array(
				'status' => 102,
				'errorMsg' => 'system error: '.$e->getMessage(),
				'timeStamp' => $timestamp,
				'providerTrnId' => -1
			);
			return $result;
		}
	}

	public function CheckTransaction($params)
	{
		$timestamp = $this->format_timestamp();
		try
		{

			if (!$this->check_login($params->username, $params->password))
			{
				return array(
					'transactionStateErrorStatus' => 601,
					'status' => 601,
					'transactionStateErrorMsg' => 'forbidden',
					'errorMsg' => 'forbidden',
					'timeStamp' => $timestamp,
					'providerTrnId' => -1,
					'transactionState' => -1
				);
			}

			$payment = ORM::factory('payment')->where('ps_transaction_id=', $params->transactionId)->find();





			if ($payment->loaded)
			{

				$fields_data = $payment->field_data('payments');



				$trn_states = '';
				for ($i = 0; $i < count($fields_data); $i++)
				{
					if ($fields_data[$i]->Field == 'status') $trn_states = $fields_data[$i]->Type;
				}
				$trn_states = explode(',', strtr($trn_states, array('enum'=>'', '('=>'', ')'=>'', "'"=>'')));


				$zor_statuses = array(
					"complete" => 1,
					"cancelled" => 2,
					"ordered" => 3,
					"expired" => 4,
				);


				$state = $zor_statuses[trim($payment->status)];

				$result = array(
					'transactionStateErrorStatus' => 0,
					'status' => 0,
					'transactionStateErrorMsg' => 'ok',
					'errorMsg' => 'ok',
					'timeStamp' => $timestamp,
					'providerTrnId' => $payment->id,
					'transactionState' => $state
				);


			}
			else
			{
				$result = array(
					'transactionStateErrorStatus' => 303,
					'status' => 303,
					'transactionStateErrorMsg' => 'invalid transactionId',
					'errorMsg' => 'invalid transactionId',
					'timeStamp' => $timestamp,
					'providerTrnId' => -1,
					'transactionState' => -1
				);
			}

			return $result;
		}
		catch (Exception $e)
		{
			Kohana::log('error', $e->getMessage());
//			throw new SoapFault('Server', $e->getMessage());
			$result = array(
				'status' => 102,
				'transactionStateErrorStatus' => 102,
				'transactionStateErrorMsg' => 'system error: '.$e->getMessage(),
				'errorMsg' => 'system error: '.$e->getMessage(),
				'timeStamp' => $timestamp,
				'providerTrnId' => -1,
				'transactionState' => -1
			);
			return $result;
		}
	}

	public function CancelTransaction($params)
	{
		$timestamp = $this->format_timestamp();
		try {
			if (!$this->check_login($params->username, $params->password)) {
				return array(
					'status' => 601,
					'errorMsg' => 'forbidden',
					'timeStamp' => $timestamp,
					'transactionState' => -1
				);
			}

			$payment = ORM::factory('payment')->where('ps_transaction_id=', $params->transactionId)->find();
			$payment2 = ORM::factory('payment')
				->where('ps_transaction_id=', $params->transactionId)
				->where('status=', 'cancelled')
				->find();

			if ($payment2->loaded) {
				$result = array(
					'status' => 202,
					'errorMsg' => 'transaction to be cancelled',
					'timeStamp' => $timestamp,
					'transactionState' => 0
				);
			}

			elseif ($payment->loaded)
			{
				$bonus_payments = ORM::factory('payment')
					->where('user_id=', $payment->user_id)
					->where('method=', 'bonus')
					->where('added>', $payment->completed)
					->find_all();
				$fields_data = $payment->field_data('payments');

				$trn_states = '';
				for ($i = 0; $i < count($fields_data); $i++)
				{
					if ($fields_data[$i]->Field == 'status') $trn_states = $fields_data[$i]->Type;
				}
				$trn_states = explode(',', strtr($trn_states, array('enum'=>'', '('=>'', ')'=>'', "'"=>'')));




				if (count($bonus_payments) == 0)
				{
					$payment->status = 'cancelled';
					$payment->save();

					$tariff = ORM::factory('tariff')->where('service=', 'bonus')->where('method=', 'paynet')->find();
					$account = ORM::factory('account')->where('user_id=', $payment->user_id)->find();

					$cancelled_bonuses = round($payment->price/$tariff->price);
					$account->bonuses -= $cancelled_bonuses;
					$account->total_bonuses -= $cancelled_bonuses;
					$account->save();

					$state = array_search('cancelled', $trn_states);
					$state++;

					$result = array(
						'status' => 0,
						'errorMsg' => 'ok',
						'timeStamp' => $timestamp,
						'transactionState' => 2
					);
				}
				else
				{
					$state = array_search($payment->status, $trn_states);
					$state++;

					$result = array(
						'status' => 203,
						'errorMsg' => 'transaction cannot be cancelled',
						'timeStamp' => $timestamp,
						'transactionState' => $state
					);
				}
			}
			else
			{
				$result = array(
					'status' => 303,
					'errorMsg' => 'invalid transactionId',
					'timeStamp' => $timestamp,
					'transactionState' => -1
				);
			}

			return $result;
		}
		catch (Exception $e)
		{
			Kohana::log('error', $e->getMessage());
//			throw new SoapFault('Server', $e->getMessage());
			$result = array(
				'status' => 102,
				'errorMsg' => 'system error: '.$e->getMessage(),
				'timeStamp' => $timestamp,
				'transactionState' => -1
			);
			return $result;
		}
	}

	public function GetStatement($params)
	{

	 	try
		{

			if (!$this->check_login($params->username, $params->password))
			{
				return array(
					'status' => 601,
					'errorMsg' => 'forbidden',
					'timeStamp' => $this->format_timestamp(),
				);
			}

			if ((!isset($params->dateFrom) || empty($params->dateFrom))
				&& (!isset($params->dateTo) || empty($params->dateTo)))
			{
				return array(
					'status' => 411,
					'errorMsg' => 'required parameters not specified',
					'timeStamp' => $this->format_timestamp(),
				);
			}


			if ((strtotime($params->dateFrom) !== FALSE)
				&& (strtotime($params->dateTo) !== FALSE))
			{


				$result = array(
					'status' => 0,
					'errorMsg' => 'ok',
					'timeStamp' => $this->format_timestamp(),
				);


				$payments = ORM::factory('payment')
					->where('service=', 'bonus')
					->where('method=', 'paynet')
					->where('status=', 'complete')
					->where('added>=', $this->format_timestamp(strtotime($params->dateFrom)))
					->where('added<=', $this->format_timestamp(strtotime($params->dateTo)))
					->find_all();

				foreach ($payments as $payment)
				{
					$result['statements'][] = array(
						'amount' => $payment->price*100,
						'providerTrnId' => $payment->id,
						'transactionId' => $payment->ps_transaction_id,
						'transactionTime' => $this->format_timestamp(strtotime($payment->added))
					);
				}
			}
			else
			{
				$result = array(
					'status' => 414,
					'errorMsg' => 'invalid date fromat',
					'timeStamp' => $this->format_timestamp(),
				);
			}


			return $result;
		}
		catch (Exception $e)
		{
			Kohana::log('error', $e->getMessage());
			$result = array(
				'status' => 102,
				'errorMsg' => 'system error: '.$e->getMessage(),
				'timeStamp' => $this->format_timestamp(),
			);

			return $result;
		}
	}


	public function GetInformation($params)
	{

		$timestamp = $this->format_timestamp();

		try
		{

			if (!$this->check_login($params->username, $params->password))
			{
				return array(
					'status' => 601,
					'errorMsg' => 'forbidden',
					'timeStamp' => $timestamp,
				);
			}

			$data = array();

			if (is_array($params->parameters)) {
				for($i = 0; $i < count($params->parameters);$i++)
				{
					$data[$params->parameters[$i]->paramKey] = $params->parameters[$i]->paramValue;
				}
			} else {
				$data[$params->parameters->paramKey] = $params->parameters->paramValue;
			}

			if (!isset($data['customer_id']) || empty($data['customer_id']))
			{

				$tariff = ORM::factory('tariff')->where('service=','bonus')->where('method=', 'paynet')->find();

				$result = array(
					'status' => 0,
					'errorMsg' => 'ok',
					'timeStamp' => $timestamp,
					'parameters' => array(
						array('paramKey'=>'bonus_price', 'paramValue'=>floor($tariff->price/$tariff->amount)*100),
					)
				);

			} else {

				if ($data['customer_id'] != strVal(intVal($data['customer_id'], 10)))
				{
					return array(
						'status' => 401,
						'errorMsg' => 'parameter validation error',
						'timeStamp' => $timestamp,
						'providerTrnId' => -1
					);
				}

				$user = ORM::factory('user')->find($data['customer_id']);

				if ($user->loaded)
				{
					$balance = $user->account->bonuses;

					$result = array(
						'status' => 0,
						'errorMsg' => 'ok',
						'timeStamp' => $timestamp,
						'parameters' => array(
//							array('paramKey'=>'customer_id', 'paramValue'=>$user->id),
							array('paramKey'=>'balance', 'paramValue'=> $balance)
						)
					);
				}
				else
				{
					$result = array(
						'status' => 302,
						'errorMsg' => 'user does not exists',
						'timeStamp' => $timestamp,
					);
				}
			}
			return $result;
		}
		catch (Exception $e)
		{
			Kohana::log('error', $e->getMessage());

			$result = array(
				'status' => 102,
				'errorMsg' => 'system error: '.$e->getMessage(),
				'timeStamp' => $timestamp,
			);
			return $result;
		}
	}


	protected function check_login($username, $password)
	{
		if ($username == Lib::config('payment.method', 'paynet', 'username')
			&& $password == Lib::config('payment.method', 'paynet', 'password'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	protected function format_timestamp($timestamp = NULL)
	{
		if ($timestamp == NULL)
		{
			$ar_time = explode(' ', microtime());
			return date('Y-m-d\TH:i:s.', $ar_time[1]).(round($ar_time[0], 3)*1000).date('P', $ar_time[1]);
		}
		else
		{
			return date('Y-m-d\TH:i:s.', $timestamp).substr(date('u', $timestamp), 0, 3).date('P', $timestamp);
		}
	}

}