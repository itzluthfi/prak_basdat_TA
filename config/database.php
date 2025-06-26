<?php
// =====================================================
// Database Configuration
// Sistem Informasi Rental Alat Pendakian
// =====================================================

class Database
{
    private $host = "localhost";
    private $db_name = "rental_alat_pendakian";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Set SQL mode untuk kompatibilitas
            $this->conn->exec("SET sql_mode = ''");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
