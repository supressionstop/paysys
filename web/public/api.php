<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/providers/db.php';
require '../src/entities/PaymentRequest.php';
require '../src/entities/Callback.php';
require '../src/entities/StatusRequest.php';


/**
 * Using for development.
 */
$config['displayErrorDetails'] = true;
/*
 * Pass managing Content-Length to web server.
 */
$config['addContentLengthHeader'] = false;

$ini = parse_ini_file('../config/config.ini');
$config['db']['host']   = $ini['mysql_host'];
$config['db']['user']   = $ini['mysql_username'];
$config['db']['pass']   = $ini['mysql_password'];
$config['db']['dbname'] = $ini['mysql_db_name'];

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

/*
 * Logger.
 */
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('api_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/api.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

/*
 *  Testing.
 */
$app->get('/api/test', function (Request $request, Response $response, array $args) {
    $this->logger->addInfo($request->getRequestTarget() . " start.");
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});

/*
 * Callback handler from Depkasa.
 */
$app->post('/api/callback/payment_system', function(Request $req, Response $resp, array $args) {

    $requireParams = array('amount', 'cardNumber', 'currency',
        'message','operation','paymentMethod', 'referenceNo',
        'requestedAmount','requestedCurrency','status','storedCardId',
        'timestamp','transactionId','type','voucherCode','token');

    if(!haveEmptyParameters($requireParams, $req, $resp))
    {
        $params = $req->getQueryParams();

        $callback = new Callback($params['amount'],
            $params['cardNumber'],
            $params['currency'],
            $params['message'],
            $params['operation'],
            $params['paymentMethod'],
            $params['referenceNo'],
            $params['requestedAmount'],
            $params['requestedCurrency'],
            $params['status'],
            $params['storedCardId'],
            $params['timestamp'],
            $params['transactionId'],
            $params['type'],
            $params['voucherCode'],
            $params['token']
            );
        $callback->Initialize();
        $returnVal = $callback->Check();

        $response = $resp->withHeader('Content-type', 'application/json');
        $body = $response->getBody();
        $body->write($returnVal);

        return $response;
    }

});

/*
 * Payment.
 */
$app->group('/api/payment', function() {
    $this->post('', function (Request $req, Response $resp, array $args) {
        $this->logger->addInfo("CALL " . $req->getRequestTarget());

        $returnVal=null;

        $requireParams = array('email', 'birthday', 'amount',
            'currency','language','billingFirstName', 'billingLastName',
            'billingAddress1','billingCity','billingPostcode','billingCountry',
            'paymentMethod','number','cvv','expiryMonth','expiryYear');

        if(!haveEmptyParameters($requireParams, $req, $resp))
        {
            $params = $req->getQueryParams();
            $this->logger->addInfo(json_encode($params));
            $paymentRequest = new PaymentRequest($params['email'],
                $params['birthday'],
                $params['amount'],
                $params['currency'],
                $params['language'],
                $params['billingFirstName'],
                $params['billingLastName'],
                $params['billingAddress1'],
                $params['billingCity'],
                $params['billingPostcode'],
                $params['billingCountry'],
                $params['paymentMethod'],
                $params['number'],
                $params['cvv'],
                $params['expiryMonth'],
                $params['expiryYear']
                );
            $returnVal= $paymentRequest->Initialize();
            $this->logger->addInfo(json_encode("Created ID: " . $paymentRequest->getReferenceNo()));
            $this->logger->addInfo(json_encode("Created Timestamp: " . $paymentRequest->getTimestamp()));

        }

        $this->logger->addInfo("END " . $req->getRequestTarget());

        $response = $resp->withHeader('Content-type', 'application/json');
        $body = $response->getBody();
        $body->write($returnVal);

        return $response;
    });

    /*
     * Request with random parameters.
     */
    $this->get('/random', function (Request $req, Response $resp, array $args){
        $this->logger->addInfo($req->getRequestTarget() . " called.");
        $paymentRequest = new PaymentRequest();
        return $req->getRequestTarget();
    });

    /*
     * Check status.
     */
    $this->get('', function (Request $req, Response $resp, array $args){
        $this->logger->addInfo($req->getRequestTarget() . " called.");
        $returnVal = null;

        $requireParams = array('referenceNo');
        if(!haveEmptyParameters($requireParams, $req, $resp))
        {
            $params = $req->getQueryParams();
            $this->logger->addInfo(json_encode($params));
            $statusRequest = new StatusRequest($params['referenceNo']);
            $returnVal = $statusRequest->Initialize();
        }

        $response = $resp->withHeader('Content-type', 'application/json');
        $body = $response->getBody();
        $body->write($returnVal);

        return $response;
    });
});

function haveEmptyParameters($required_params, $request, $response){
    $error = false;
    $error_params = '';
    $request_params = $request->getQueryParams();
    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true;
            $error_params .= $param . ', ';
        }
    }
    if($error){
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;
}

$app->run();