<?php

class PaymentDetails
{
    // Fields.
    public $transactionId;
    public $currency;
    public $amount;
    public $idForeignSystem;

    public function __construct($transactionId, $currency, $amount, $idForeignSystem)
    {
        $this->transactionId = $transactionId;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->idForeignSystem = $idForeignSystem;
    }

    public function Initialize()
    {
        $db = new Connection();
        $initArr = $db->InitPaymentRequestDetails($this);
    }
}