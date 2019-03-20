<?php
class Database{

    // Fields.
    private $host;
    private $dbName;
    private $username;
    private $password;
    public $conn;

    // Properties.
    public function getHost(){
        return $this->host;
    }

    public function setHost($host){
        $this->host = $host;
    }

    public function getDbName(){
        return $this->dbName;
    }

    public function setDbName($dbName){
        $this->db_name = $dbName;
    }

    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getPassword(){
        return $this->password;
    }

    public function setPassword($password){
        $this->password = $password;
    }

    public function __construct()
    {
        $ini = parse_ini_file('config.ini');
        $this->setHost($ini['mysql_host']);
        $this->setDbName($ini['mysql_db_name']);
        $this->setUsername($ini['mysql_username']);
        $this->setPassword($ini['mysql_password']);
    }

    // get the database connection
    public function getConnection(){

        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->getHost() . ";dbname=" . $this->getDbName(), $this->getUsername(), $this->getPassword());
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>