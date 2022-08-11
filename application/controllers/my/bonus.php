<?php defined('SYSPATH') or die('No direct script access.');

class Bonus_Controller extends Controller_Core {

    /**
     * переменная для хранения информации о запросе
     *
     * @var array
     */
    protected $order;
    /**
     * способ оплаты или платежная система, в данный момент поддерживается только Gateway Technologies
     * система платежей с помощью пластиковой сумовой online-карточки
     * @var string
     *
     */
    protected $payment_method;

    public function __construct()
    {
        parent::__construct();
        ini_set('display_errors', 1);
        error_reporting(E_ALL ^ E_NOTICE);

        $this->order = array();
        $this->payment_method = 'gw_tech';

        $this->log_filename = $_SERVER['DOCUMENT_ROOT'].'/bonus.log';
    }

    /**
     * прокси-обработчик для запроса к платежной системе
     * получает запрос от формы с требуемым количеством бонусов,
     * прописывает информацию о транзакции в БД,
     * генерирует набор данных необходимых для запроса к платежной системе
     * и переадресовывает запрос
     *
     */
    public function request_payment()
    {
        $payment_date = date('Ymd');
        $payment_ts = date('Y-m-d H:i:s');
        try
        {
            $_POST['bonus_cnt'] = 1;
            $testPaymentSystem = PaymentSystem::getPaymentSystem('test');

            $_POST = new Validation($_POST);
            $_POST->pre_filter('trim', true)
                    ->add_rules('bonus_cnt', 'required')
                    ->post_filter('floatVal', 'bonus_cnt')
                    ;

            $tariff = ORM::factory('tariff')
                    ->where('service', 'bonus')
                    ->where('method', $this->payment_method)
                    ->find()
                    ;
            // вычисляем сумму платежа
            $payment_sum = $_POST->bonus_cnt*$tariff->price/$tariff->amount;

            $user = Auth::instance()->authorize();

            // прописываем в БД транзакцию
            $payment = ORM::factory('payment');
            $payment->user_id = $user->id;
            $payment->added = $payment_ts;
            $payment->updated = $payment_ts;
//            $payment->service = 'bonus';
            $payment->method = $this->payment_method;
            $payment->price = $payment_sum;
            $payment->currency = 'uzs';
            $payment->final_price = $payment->price;
            $payment->final_currency = strtolower($testPaymentSystem->currency_code);
            $payment->details = 'Покупка ' . format::declension_numerals($_POST->bonus_cnt, 'бонуса','бонусов','бонусов') . '. Оплата через платежню систему Gateway Technologies';
            $payment->units_bought = $_POST->bonus_cnt;
//            $payment->ps_transaction_id = NULL;
            $payment->status = 'ordered';
//            $payment->completed = date('Y-m-d H:i:s');
            $payment->save();

            $this->order = array(
                'amount' => $payment_sum,
                'date' => $payment_date,
                'id' => $payment->id,
            );

            // запоминаем ID транзакции
            Session::instance()->set('payment_id', $payment->id);

            $request_url = $testPaymentSystem->buildPaymentRequestURL($this->order);


//            echo $request_url;
//            Kohana::log('error', $request_url);

            url::redirect($request_url);
        }
        catch (Exception $e)
        {
            Kohana::log('error', $e->getMessage().': '.$e->getTraceAsString());
        }
    }

    /**
     * обработчик ответа от платежной системы
     *
     */
    public function handle_payment_response()
    {
//        этот параметр можно задавать только в php.ini
//        или главном кофиге веб-сервера через php_amin_value
//        ini_set('suhosin.get.max_value_length', 1024);
        try
        {
            $testPaymentSystem = PaymentSystem::getPaymentSystem('test');

            $payment_status = $testPaymentSystem->verifyResponse();

            // определяем ID текущей транзакции, ответ в рамках которой надо обработать
            $payment_id = Sessin::instance()->get('payment_id', FALSE);
            if ($payment_id)
            {
                $payment = ORM::factory('payment', $payment_id);

                // если такая транзакция существует
                if ($payment->loaded)
                {
                    // если платеж успешно проведен
                    if ($payment_status === 1)
                    {
                        $payment->updated = date('Y-m-d H:i:s');
                        $payment->completed = date('Y-m-d H:i:s');
                        $payment->status = 'complete';
                    }
                    //  неверная цифровая подпись
                    else
                    if ($payment_status === -1)
                    {
                        $payment->updated = date('Y-m-d H:i:s');
                        $payment->completed = date('Y-m-d H:i:s');
                        $payment->status = 'cancelled';
                    }

                    if ($payment->changed)
                    {
                        $payment->save();
                    }
                }
            }
        }
        catch (Exception $e)
        {
            Kohana::log('error', $e->getMessage().': '.$e->getTraceAsString());
        }
    }

    public function request_status()
    {
        try
        {
            $_POST['payment_id'] = '1732';
            $testPaymentSystem = PaymentSystem::getPaymentSystem('test');

            $_POST = new Validation($_POST);
            $_POST->pre_filter('trim', true)
                    ->add_rules('payment_id', 'required')
                    ->post_filter('intVal', 'payment_id')
                    ;

            $payment = ORM::factory('payment', $_POST->payment_id);

            // если такая транзакция существует
            if ($payment->loaded)
            {
                $this->order = array(
                    'id' => $payment->id,
//                    'amount' => $payment->price,
                    'date' => date('Ymd', strtotime($payment->added)),
                );
                $request_url = $testPaymentSystem->buildPaymentStatusURL($this->order);

    //            Kohana::log('error', $request_url);

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

                $payment_status = $testPaymentSystem->verifyStatusResponse($response);

                if ($payment_status)
                {
                    // если платеж успешно проведен
                    if ($payment_status === 1)
                    {
                        $payment->updated = date('Y-m-d H:i:s');
                        $payment->completed = date('Y-m-d H:i:s');
                        $payment->status = 'complete';

                        $payment->save();
                    }
                    else
                    {
                        echo $payment_status;
                    }
                }
                else
                {
                    echo 'processing...';
                }
//                echo (!$payment_status) ? 'processing' : 'complete';
            }
        }
        catch (Exception $e)
        {
            Kohana::log('error', $e->getMessage().': '.$e->getTraceAsString());
        }
    }
}
?>
