<?php

class StatusRequest
{
    // Fields.
    public $apiKey;
    public $referenceNo;

    public function __construct($referenceNo)
    {
        $ini = parse_ini_file('../config/config.ini');
        $this->apiKey =  $ini['api_key'];
        $this->referenceNo = $referenceNo;
    }

    public function Initialize()
    {
        $depkasa = new Depkasa();
        $json = $depkasa->CheckPayment($this->referenceNo);

        return $json;
    }
}