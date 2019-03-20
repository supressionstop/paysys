<?php

class Connection
{
    protected $db =null;

    public function Open()
    {
        try {
            $ini = parse_ini_file('../config/config.ini');
            $host = $ini['mysql_host'];
            $dbName = $ini['mysql_db_name'];
            $username = $ini['mysql_username'];
            $password = $ini['mysql_password'];
            $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE                      => PDO::FETCH_ASSOC,
        );

            $this->db = new PDO('mysql:host='.$host.';dbname='.$dbName, $username, $password, $options);

            return $this->db;
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public function Close()
    {
        $this->db = null;
        return true;
    }

    public function InitPaymentRequest(PaymentRequest $paymentRequest)
    {
        $con = $this->Open();

        if($con){
            // Status Init
            $status = 1;
            $statement = $con->prepare("INSERT INTO `transactions`
                    (`status`) 
                    VALUES (:status)");
            $statement->bindParam(":status",$status,PDO::PARAM_INT);
            $statement->execute();
            $idReturn = $con->lastInsertId();
            $paymentRequest->setReferenceNo($idReturn);

            $statement = $con->prepare("SELECT UNIX_TIMESTAMP(`status_timestamp`)  AS `status_timestamp` FROM `transactions` WHERE id = :id");
            $statement->bindParam(":id",$idReturn);
            $statement->execute();
            $timeStamp = $statement->fetch();
            $paymentRequest->setTimestamp($timeStamp['status_timestamp']);
        }

        $this->Close();

        $this->StatusHistoryUpdate($paymentRequest->getReferenceNo(),1, array());

        return array("status"=>$paymentRequest->getReferenceNo(),
            "status_timestamp"=>$paymentRequest->getTimestamp());
    }

    public function InitPaymentRequestStatus(PaymentRequest $paymentRequest, PaymentResponse $paymentResponse)
    {
        $con = $this->Open();

        if($con){
            // Status Delivered
            $status = 2;
            $statement = $con->prepare("UPDATE `transactions` SET `status`=:status WHERE `id`=:id");
            $statement->bindParam(":status",$status,PDO::PARAM_INT);
            $statement->bindParam(":id",$paymentRequest->getReferenceNo(), PDO::PARAM_INT);
            $statement->execute();

            $statement = $con->prepare("INSERT INTO `transaction_details`(`transaction_id`, `amount`, `currency`, `id_foreign_system`) 
                                                  VALUES (:id,:amount,:currency,:id_foreign)");
            $statement->bindParam(":id",$paymentResponse->getReferenceNo());
            $statement->bindParam(":amount",$paymentResponse->getAmount());
            $statement->bindParam(":currency",$paymentResponse->getCurrency());
            $statement->bindParam(":id_foreign",$paymentResponse->getTransactionId());
            $statement->execute();
            $timeStamp = $statement->fetch();
            $paymentRequest->setTimestamp($timeStamp['status_timestamp']);

            $this->StatusHistoryUpdate($paymentResponse->getReferenceNo(),1,$status);
        }

        $this->Close();
    }

    public function InitPaymentRequestDetails(PaymentDetails $paymentDetails)
    {
        $con = $this->Open();

        if($con)
        {
            $statement = $con->prepare("INSERT INTO `transaction_details`(`transaction_id`, `amount`, `currency`, `id_foreign_system`) 
                                                  VALUES (:transactionId, :amount, :currency, :idForeignSystem)");
            $statement->bindParam(":transactionId", $paymentDetails->transactionId);
            $statement->bindParam(":amount", $paymentDetails->amount);
            $statement->bindParam(":currency", $paymentDetails->currency);
            $statement->bindParam(":idForeignSystem", $paymentDetails->idForeignSystem);
            $statement->execute();
        }

        $this->Close();
    }

    public function UpdatePaymentRequestDetails($id, $idForeignSystem)
    {
        $con = $this->Open();

        if($con)
        {
            $statement = $con->prepare("UPDATE `transaction_details` SET `id_foreign_system`= :idForeignSystem 
                                                  WHERE `transaction_id`= :transactionId");
            $statement->bindParam(":transactionId", $id);
            $statement->bindParam(":idForeignSystem", $idForeignSystem);
            $statement->execute();
        }

        $this->Close();
    }

    public function StatusUpdate($id, $to, $errors)
    {
        $con = $this->Open();

        $from = null;

        if($con){
            $statement = $con->prepare("UPDATE `transactions` SET `status`=:status, `status_timestamp`=NOW() WHERE `id` = :transactionId");
            $statement->bindParam(":transactionId", $id);
            $statement->bindParam(":status", $to);
            $statement->execute();
        }

        $this->StatusHistoryUpdate($id, $to, $errors);

        $this->Close();
    }


    private function StatusHistoryUpdate($id, $to, $errors)
    {
        $con = $this->Open();

        if($con){

            $statement = $con->prepare("SELECT `to` FROM `transaction_statuses_history` WHERE `transaction_id`= :transactionId ORDER BY id DESC LIMIT 1 ");
            $statement->bindParam(":transactionId", $id);
            $statement->execute();
            $fromDb = $statement->fetch();
            $from = $fromDb['to'];

            $statement = $con->prepare("INSERT INTO `transaction_statuses_history`(`transaction_id`, `change_time`, `from`, `to`, `error_code`, `error_description`) 
                                                  VALUES (:id,NOW(),:from,:to,:errorCode,:errorDescription)
                                                  ON DUPLICATE KEY 
                                                  UPDATE `change_time`=NOW(),`from`=:from, `to`=:to ");
            $statement->bindParam(":id",$id);
            $statement->bindParam(":from",$from);
            $statement->bindParam(":to",$to);
            $statement->bindParam(":errorCode",$errors['errorCode']);
            $statement->bindParam(":errorDescription",$errors['errorDescription']);

            $statement->execute();
        }

        $this->Close();
    }

}