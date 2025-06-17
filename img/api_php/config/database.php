<?php
// api_php/config/database.php

class DatabaseConfig {
    // Database credentials
    private $host = "localhost"; // Généralement 'localhost' ou l'IP de votre serveur DB
    private $db_name = "ai_one_db"; // Le nom de votre base de données
    private $username = "root"; // Votre nom d'utilisateur MySQL/MariaDB
    private $password = ""; // Votre mot de passe MySQL/MariaDB

    // Getter methods for database credentials
    public function getHost() {
        return $this->host;
    }

    public function getDbName() {
        return $this->db_name;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }
}
?>