<?php

require "/usr/share/nginx/web/src/providers/Depkasa.php";
//require "/usr/share/nginx/web/src/providers/db.php";
require "PaymentDetails.php";

class PaymentRequest
{
    // Fields.
    private $email;
    private $birthday;
    private $amount;
    private $currency;
    private $referenceNo;
    private $timestamp;
    private $language;
    private $billingFirstName;
    private $billingLastName;
    private $billingAddress1;
    private $billingCity;
    private $billingPostcode;
    private $billingCountry;
    private $paymentMethod;
    private $number;
    private $cvv;
    private $expiryMonth;
    private $expiryYear;
    private $idTransaction;

    // Properties.
    public function getEmail(){
        return $this->email;
    }

    public function setEmail($email){
        $this->email = $email;
    }

    public function getBirthday(){
        return $this->birthday;
    }

    public function setBirthday($birthday){
        $this->birthday = $birthday;
    }

    public function getAmount(){
        return $this->amount;
    }

    public function setAmount($amount){
        $this->amount = $amount;
    }

    public function getCurrency(){
        return $this->currency;
    }

    public function setCurrency($currency){
        $this->currency = $currency;
    }

    public function getReferenceNo(){
        return $this->referenceNo;
    }

    public function setReferenceNo($referenceNo){
        $this->referenceNo = $referenceNo;
    }

    public function getTimestamp(){
        return $this->timestamp;
    }

    public function setTimestamp($timestamp){
        $this->timestamp = $timestamp;
    }

    public function getLanguage(){
        return $this->language;
    }

    public function setLanguage($language){
        $this->language = $language;
    }

    public function getBillingFirstName(){
        return $this->billingFirstName;
    }

    public function setBillingFirstName($billingFirstName){
        $this->billingFirstName = $billingFirstName;
    }

    public function getBillingLastName(){
        return $this->billingLastName;
    }

    public function setBillingLastName($billingLastName){
        $this->billingLastName = $billingLastName;
    }

    public function getBillingAddress1(){
        return $this->billingAddress1;
    }

    public function setBillingAddress1($billingAddress1){
        $this->billingAddress1 = $billingAddress1;
    }

    public function getBillingCity(){
        return $this->billingCity;
    }

    public function setBillingCity($billingCity){
        $this->billingCity = $billingCity;
    }

    public function getBillingPostcode(){
        return $this->billingPostcode;
    }

    public function setBillingPostcode($billingPostcode){
        $this->billingPostcode = $billingPostcode;
    }

    public function getBillingCountry(){
        return $this->billingCountry;
    }

    public function setBillingCountry($billingCountry){
        $this->billingCountry = $billingCountry;
    }

    public function getPaymentMethod(){
        return $this->paymentMethod;
    }

    public function setPaymentMethod($paymentMethod){
        $this->paymentMethod = $paymentMethod;
    }

    public function getNumber(){
        return $this->number;
    }

    public function setNumber($number){
        $this->number = $number;
    }

    public function getCvv(){
        return $this->cvv;
    }

    public function setCvv($cvv){
        $this->cvv = $cvv;
    }

    public function getExpiryMonth(){
        return $this->expiryMonth;
    }

    public function setExpiryMonth($expiryMonth){
        $this->expiryMonth = $expiryMonth;
    }

    public function getExpiryYear(){
        return $this->expiryYear;
    }

    public function setExpiryYear($expiryYear){
        $this->expiryYear = $expiryYear;
    }

    public function getIdTransaction(){
        return $this->idTransaction;
    }

    public function setIdTransaction($idTransaction){
        $this->idTransaction = $idTransaction;
    }

    public function __construct($email, $birthday, $amount,
                                $currency, /*$referenceNo, $timestamp,*/
                                $language, $billingFirstName, $billingLastName,
                                $billingAddress1, $billingCity, $billingPostcode,
                                $billingCountry, $paymentMethod, $number,
                                $cvv, $expiryMonth, $expiryYear)
    {
        $this->setEmail($email);
        $this->setBirthday($birthday);
        $this->setAmount($amount);
        $this->setCurrency($currency);
//        $this->setReferenceNo($referenceNo);
//        $this->setTimestamp($timestamp);
        $this->setLanguage($language);
        $this->setBillingFirstName($billingFirstName);
        $this->setBillingLastName($billingLastName);
        $this->setBillingAddress1($billingAddress1);
        $this->setBillingCity($billingCity);
        $this->setBillingPostcode($billingPostcode);
        $this->setBillingCountry($billingCountry);
        $this->setPaymentMethod($paymentMethod);
        $this->setNumber($number);
        $this->setCvv($cvv);
        $this->setExpiryMonth($expiryMonth);
        $this->setExpiryYear($expiryYear);
    }

    public function Initialize(){
        $db = new Connection();
        $initArr = $db->InitPaymentRequest($this);

        $this->setReferenceNo($initArr['status']);
        $this->setTimestamp($initArr['status_timestamp']);

//        $db->StatusUpdate($this->referenceNo,1);

        $paymentDetails = new PaymentDetails($this->getReferenceNo(),
            $this->getCurrency(),
            $this->getAmount(),
            "");
        $db->InitPaymentRequestDetails($paymentDetails);

        $depkasa = new Depkasa();
        $depkasa->Prepare($this);

        $json = $depkasa->MakePayment();

        return $json;
    }
}