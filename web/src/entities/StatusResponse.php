<?php

class StatusResponse
{
    // Fields
    public $code;
    public $status;
    public $message;
    public $referenceNo;
    public $transactionId;
    public $amount;
    public $currency;

    public function __construct($code,$status,$message,$referenceNo,$transactionId,$amount,$currency)
    {
        $this->code = $code;
        $this->status = $status;
        $this->message = $message;
        $this->referenceNo = $referenceNo;
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->currency = $currency;
    }
}