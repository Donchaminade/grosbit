<?php
// api_php/includes/Database.php

require_once __DIR__ . '/../config/database.php';

class Database {
    private $conn;
    private $config;

    public function __construct() {
        $this->config = new DatabaseConfig();
    }

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->config->getHost() . ";dbname=" . $this->config->getDbName();
            $this->conn = new PDO($dsn, $this->config->getUsername(), $this->config->getPassword());
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exceptions
        } catch(PDOException $exception) {
            // Log the error for debugging purposes (optional, good practice)
            error_log("Connection error: " . $exception->getMessage());
            // In a production environment, you might want a more generic error
            // to the client, but for development, showing the error is fine.
            echo json_encode(array("message" => "Database connection error: " . $exception->getMessage()));
            exit(); // Stop execution
        }

        return $this->conn;
    }
}
?>