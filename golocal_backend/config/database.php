<?php
class Database {

    // --- IMPORTANT: UPDATE YOUR DATABASE DETAILS HERE ---
    private $host = "localhost";
    private $db_name = "golocal_db";
    private $username = "root";
    private $password = ""; // Use your MySQL root password if you have one
    // ----------------------------------------------------

    public $conn;

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to throw exceptions
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
