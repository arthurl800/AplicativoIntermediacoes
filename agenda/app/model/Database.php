<?php

    include_once __DIR__ . '/../../config/dbconfig.php';

    class Database{

        private $servername;
        private $username;
        private $password;
        private $dbname;
        private $conn;

        private static $instance = null;


        function __construct()
        {
            $this->servername = DB_HOST;
            $this->username = DB_USER;
            $this->password = DB_PASSWORD;
            $this->dbname = DB_NAME;
        }


        // Método para obter a instância única da classe
        public static function getInstance(){
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }


        function connect(){
            $this->conn = new mysqli($this->servername,$this->username,$this->password, $this->dbname);

            if ($this->conn->connect_error) {
                die("Falha na conexão com o banco de dados: " . $this->conn->connect_error);
            }

            return $this->conn;
        }

        function closeConnection(){
            if ($this->conn) {
                $this->conn->close();
            }
        }

        // Impedir que a instância seja clonada
        private function __clone(){}

        // Impedir que instância seja desserializada
        private function __wakeup(){}


        
    }



?>