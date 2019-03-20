<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Depkasa
{

    // Fields.
    private $apiKey;
    private $secretKey;
    private $paymentUrl;
    private $paymentDetailUrl;
    private $returnUrl;
    private $callbackUrl;
    private $token;
    private $paymentRequest;

    // Properties.
    public function getApiKey(){
        return $this->apiKey;
    }

    public function setApiKey($apiKey){
        $this->apiKey = $apiKey;
    }

    public function getSecretKey(){
        return $this->secretKey;
    }

    public function setSecretKey($secretKey){
        $this->secretKey = $secretKey;
    }

    public function getPaymentUrl(){
        return $this->paymentUrl;
    }

    public function setPaymentUrl($paymentUrl){
        $this->paymentUrl = $paymentUrl;
    }

    public function getPaymentDetailUrl(){
        return $this->paymentDetailUrl;
    }

    public function setPaymentDetailUrl($paymentDetailUrl){
        $this->paymentDetailUrl = $paymentDetailUrl;
    }

    public function getReturnUrl(){
        return $this->returnUrl;
    }

    public function setReturnUrl($returnUrl){
        $this->returnUrl = $returnUrl;
    }

    public function getCallbackUrl(){
        return $this->callbackUrl;
    }

    public function setCallbackUrl($callbackUrl){
        $this->callbackUrl = $callbackUrl;
    }

    public function getToken(){
        return $this->token;
    }

    public function setToken($token){
        $this->token = $token;
    }

    public function getPaymentRequest(){
        return $this->paymentRequest;
    }

    public function setPaymentRequest(PaymentRequest $paymentRequestt){
        $this->paymentRequest = $paymentRequestt;
    }

    public function __construct()
    {
        $ini = parse_ini_file('../config/config.ini');

        $this->setApiKey($ini['api_key']);
        $this->setSecretKey($ini['secret_key']);
        $this->setPaymentUrl($ini['payment_url']);
        $this->setPaymentDetailUrl($ini['payment_detail_url']);
        $this->setReturnUrl($ini['return_url']);
        $this->setCallbackUrl($ini['callback_url']);
    }

    public function Prepare(PaymentRequest $paymentRequest)
    {
        $this->setPaymentRequest($paymentRequest);
        $this->setToken($this->generateToken());
    }

    public function MakePayment()
    {
        // create a log channel
        $log = new Logger('MakePayment()');
        $log->pushHandler(new StreamHandler('/usr/share/nginx/web/logs/api.log', Logger::INFO));

        $db = new Connection();
        $curl = curl_init();

        $curlParams = array(
            'token' => $this->getToken(),
            'apiKey' => $this->getApiKey(),
            'email' => $this->paymentRequest->getEmail(),
            'birthday' => $this->paymentRequest->getBirthday(),
            'amount' => $this->paymentRequest->getAmount(),
            'currency' => $this->paymentRequest->getCurrency(),
            'returnUrl' => $this->getReturnUrl(),
            'referenceNo' => $this->paymentRequest->getReferenceNo(),
            'timestamp' => $this->paymentRequest->getTimestamp(),
            'language' => $this->paymentRequest->getLanguage(),
            'billingFirstName' => $this->paymentRequest->getBillingFirstName(),
            'billingLastName' => $this->paymentRequest->getBillingLastName(),
            'billingAddress1' => $this->paymentRequest->getBillingAddress1(),
            'billingCity' => $this->paymentRequest->getBillingCity(),
            'billingPostcode' => $this->paymentRequest->getBillingPostcode(),
            'billingCountry' => $this->paymentRequest->getBillingCountry(),
            'paymentMethod' => $this->paymentRequest->getPaymentMethod(),
            'number' => $this->paymentRequest->getNumber(),
            'cvv' => $this->paymentRequest->getCvv(),
            'expiryMonth' => $this->paymentRequest->getExpiryMonth(),
            'expiryYear' => $this->paymentRequest->getExpiryYear(),
            'callbackUrl' => $this->getCallbackUrl()
        );

        $log->info($this->getPaymentUrl().'?'.http_build_query($curlParams));

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getPaymentUrl().'?'.http_build_query($curlParams),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));

        $db->StatusUpdate($this->paymentRequest->getReferenceNo(), 2,array());
        $response = curl_exec($curl);

        $json = substr( $response, strpos($response,'{'));
        $jsonDecoded = json_decode($json, true);

        // Errors.
        if ($jsonDecoded['error_code'])
        {
            $errors = array('errorCode'=>$jsonDecoded['error_code'],
                'errorDescription'=>$jsonDecoded['error_description']);
            $db->StatusUpdate($this->paymentRequest->getReferenceNo(), 7,$errors);

            return $json;
        }

        // Fine.
        $db->UpdatePaymentRequestDetails($this->paymentRequest->getReferenceNo(),
            $jsonDecoded['transactionId']);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $log->info(json_encode($response));
            $db->StatusUpdate($this->paymentRequest->getReferenceNo(), 3,array());
            $db->StatusUpdate($this->paymentRequest->getReferenceNo(), 4,array());

            $json = substr( $response, strpos($response,'{'));

            return $json;
        }
    }

    public function CheckPayment($transactionId)
    {
        // Logging.
        $log = new Logger('CheckPayment()');
        $log->pushHandler(new StreamHandler('/usr/share/nginx/web/logs/api.log', Logger::INFO));

        $db = new Connection();
        $curl = curl_init();

        $curlParams = array(
            'apiKey' => $this->getApiKey(),
            'referenceNo' => $transactionId
        );

        $log->info($this->getPaymentDetailUrl().'?'.http_build_query($curlParams));

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getPaymentDetailUrl().'?'.http_build_query($curlParams),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));

        $db->StatusUpdate($transactionId, 2,array());
        $response = curl_exec($curl);
        $err = curl_error($curl);

        $json = substr( $response, strpos($response,'{'));
        $jsonDecoded = json_decode($json, true);


        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $db->StatusUpdate($transactionId, 3,array());
            return $response;
        }
    }

    private function generateToken(){
        $rawHash = $this->secretKey.
            $this->getApiKey().
            $this->paymentRequest->getAmount().
            $this->paymentRequest->getCurrency().
            $this->paymentRequest->getReferenceNo().
            $this->paymentRequest->getTimestamp();
        return md5($rawHash);
    }
}