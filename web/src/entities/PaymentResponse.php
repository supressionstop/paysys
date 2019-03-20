<?php

class PaymentResponse
{
    // Fields.
    public $amount;
    public $currency;
    public $message;
    public $paymentMethod;
    public $referenceNo;
    public $returnForm;
    public $returnUrl;
    public $status;
    public $token;
    public $transactionId;

    // Propertiespublic
    public function __construct($amount, $currency,$message,$paymentMethod,$referenceNo
                                ,$returnForm,$returnUrl,$status,$token,$transactionId)
    {
        $this->setReferenceNo = $referenceNo;
        $this->setCurrency = $currency;
        $this->setAmount = $amount;
        $this->setMessage = $message;
        $this->setPaymentMethod = $paymentMethod;
        $this->setReturnForm = $returnForm;
        $this->setReturnUrl = $returnUrl;
        $this->setStatus = $status;
        $this->setToken = $token;
        $this->setTransactionId = $transactionId;
    }

    public function Initialize()
    {
        $db = new Connection();
        $con = $db->Open();

        //
        if($con){
            // Status Init
            $status = 1;
            $statement = $con->prepare("INSERT INTO `transactions`
                    (`status`) 
                    VALUES (:status)");
            $statement->bindParam(":status",$status,PDO::PARAM_INT);
            $statement->execute();
            $idReturn = $con->lastInsertId();
            $this->setReferenceNo($idReturn);

            $statement = $con->prepare("SELECT UNIX_TIMESTAMP(`status_timestamp`)  AS `status_timestamp` FROM `transactions` WHERE id = :id");
            $statement->bindParam(":id",$idReturn);
            $statement->execute();
            $timeStamp = $statement->fetch();
            $this->setTimestamp($timeStamp['status_timestamp']);
        }
        $db->Close();
    }
}