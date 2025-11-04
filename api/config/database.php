<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "mi_tienda";
    private $port = "3307";
    private $conn;

    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
        if ($this->conn->connect_error) {
            die(json_encode(["error" => "Conexión fallida: " . $this->conn->connect_error]));
        }
        return $this->conn;
    }
}
?>

