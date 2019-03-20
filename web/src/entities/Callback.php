<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Callback
{
    // Fields.
    public $amount;
    public $cardNumber;
    public $currency;
    public $message;
    public $operation;
    public $paymentMethod;
    public $referenceNo;
    public $requestedAmount;
    public $requestedCurrency;
    public $status;
    public $storedCardId;
    public $timestamp;
    public $transactionId;
    public $type;
    public $voucherCode;
    public $token;


    public function __construct($amount, $cardNumber, $currency,
        $message,$operation,$paymentMethod, $referenceNo,
        $requestedAmount,$requestedCurrency,$status,$storedCardId,
        $timestamp,$transactionId,$type,$voucherCode,$token)
    {
        $this->amount=$amount;
        $this->cardNumber = $cardNumber;
        $this->currency = $currency;
        $this->message = $message;
        $this->operation = $operation;
        $this->paymentMethod = $paymentMethod;
        $this->referenceNo = $referenceNo;
        $this->requestedAmount = $requestedAmount;
        $this->requestedCurrency = $requestedCurrency;
        $this->status = $status;
        $this->storedCardId = $storedCardId;
        $this->timestamp = $timestamp;
        $this->transactionId = $transactionId;
        $this->type = $type;
        $this->voucherCode = $voucherCode;
        $this->token = $token;
    }

    public function Initialize()
    {
        $db = new Connection();

        $db->StatusUpdate($this->referenceNo,5,array());

    }

    public function Check()
    {
        // Logging.
        $log = new Logger('Callback->Check()');
        $log->pushHandler(new StreamHandler('/usr/share/nginx/web/logs/api.log', Logger::INFO));

        $returnVal = null;

        $db = new Connection();

        $log->info("Status: " . $this->status);

        if ($this->status == 'APPROVED')
        {
            $db->StatusUpdate($this->referenceNo,6,array());
        }
        elseif ($this->status == 'DECLINED')
        {
            $db->StatusUpdate($this->referenceNo,7,array());
        }
        elseif ($this->status == 'PENDING')
        {
            $db->StatusUpdate($this->referenceNo,4,array());
            $statusRequest = new StatusRequest($this->referenceNo);

            for ($request = 0; $request < 10; $request++)
            {
                sleep(2);
                $json = $statusRequest->Initialize();
                $returnVal = $json;
                $log->info("json " . $json);
                $decoded = json_decode($json, true);
                if ($decoded['status'] == 'APPROVED')
                {
                    $db->StatusUpdate($this->referenceNo,6,array());
                    break;
                }
                elseif ($decoded['status'] == 'DECLINED')
                {
                    $db->StatusUpdate($this->referenceNo,7,array());
                    break;
                }
            }
        }

        return $returnVal;
    }
}
