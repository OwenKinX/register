<?php 
    class Database {
        private $hostname = "localhost";
        private $username = "laogw";
        private $password = "Abc@2022";
        private $database = "laogwdb";

        public function dbConnect(){
            try {
                $con = new PDO("mysql:host=".$this->hostname."; dbname=".$this->database,$this->username,$this->password);
                $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $con;
            }catch(PDOException $e){
                echo "Connection error ".$e->getMessage(); 
                exit;
            }
        }
    }
?>
