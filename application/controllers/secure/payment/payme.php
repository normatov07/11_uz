<?php


class Payme_Controller extends Controller_Core {

    public function __construct()
    {
        parent::__construct();
        $this->auto_render = false;
    }
    public function index()
    {
        $url = 'https://172.16.100.2/secure/soap_data/ProviderWebService.wsdl';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);


        echo 'Ошибка curl: ' . curl_error($ch);
        curl_close($ch);
        print_r($result);
    }


}
