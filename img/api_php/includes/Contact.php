<?php
// api_php/includes/Contact.php

class Contact {
    // Database connection and table name
    private $conn;
    private $table_name = "contacts";

    // Object properties
    public $id;
    public $nom_complet;
    public $profession;
    public $numero_telephone;
    public $adresse_email;
    public $adresse;
    public $entreprise_organisation;
    public $date_naissance;
    public $tags_labels;
    public $notes_specifiques;
    public $created_at;
    public $updated_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all contacts
    public function read() {
        // Select all query
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Read single contact
    public function readOne() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind ID of contact to be read
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set values to object properties
        if ($row) {
            $this->nom_complet = $row['nom_complet'];
            $this->profession = $row['profession'];
            $this->numero_telephone = $row['numero_telephone'];
            $this->adresse_email = $row['adresse_email'];
            $this->adresse = $row['adresse'];
            $this->entreprise_organisation = $row['entreprise_organisation'];
            $this->date_naissance = $row['date_naissance'];
            $this->tags_labels = $row['tags_labels'];
            $this->notes_specifiques = $row['notes_specifiques'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Create contact
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " SET
                    nom_complet=:nom_complet, profession=:profession, numero_telephone=:numero_telephone,
                    adresse_email=:adresse_email, adresse=:adresse, entreprise_organisation=:entreprise_organisation,
                    date_naissance=:date_naissance, tags_labels=:tags_labels, notes_specifiques=:notes_specifiques";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->nom_complet = htmlspecialchars(strip_tags($this->nom_complet));
        $this->profession = htmlspecialchars(strip_tags($this->profession));
        $this->numero_telephone = htmlspecialchars(strip_tags($this->numero_telephone));
        $this->adresse_email = htmlspecialchars(strip_tags($this->adresse_email));
        $this->adresse = htmlspecialchars(strip_tags($this->adresse));
        $this->entreprise_organisation = htmlspecialchars(strip_tags($this->entreprise_organisation));
        $this->date_naissance = htmlspecialchars(strip_tags($this->date_naissance));
        $this->tags_labels = htmlspecialchars(strip_tags($this->tags_labels));
        $this->notes_specifiques = htmlspecialchars(strip_tags($this->notes_specifiques));

        // Bind values
        $stmt->bindParam(":nom_complet", $this->nom_complet);
        $stmt->bindParam(":profession", $this->profession);
        $stmt->bindParam(":numero_telephone", $this->numero_telephone);
        $stmt->bindParam(":adresse_email", $this->adresse_email);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":entreprise_organisation", $this->entreprise_organisation);
        $stmt->bindParam(":date_naissance", $this->date_naissance);
        $stmt->bindParam(":tags_labels", $this->tags_labels);
        $stmt->bindParam(":notes_specifiques", $this->notes_specifiques);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update contact
    public function update() {
        // Update query
        $query = "UPDATE " . $this->table_name . " SET
                    nom_complet=:nom_complet,
                    profession=:profession,
                    numero_telephone=:numero_telephone,
                    adresse_email=:adresse_email,
                    adresse=:adresse,
                    entreprise_organisation=:entreprise_organisation,
                    date_naissance=:date_naissance,
                    tags_labels=:tags_labels,
                    notes_specifiques=:notes_specifiques,
                    updated_at=CURRENT_TIMESTAMP
                  WHERE id = :id";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->nom_complet = htmlspecialchars(strip_tags($this->nom_complet));
        $this->profession = htmlspecialchars(strip_tags($this->profession));
        $this->numero_telephone = htmlspecialchars(strip_tags($this->numero_telephone));
        $this->adresse_email = htmlspecialchars(strip_tags($this->adresse_email));
        $this->adresse = htmlspecialchars(strip_tags($this->adresse));
        $this->entreprise_organisation = htmlspecialchars(strip_tags($this->entreprise_organisation));
        $this->date_naissance = htmlspecialchars(strip_tags($this->date_naissance));
        $this->tags_labels = htmlspecialchars(strip_tags($this->tags_labels));
        $this->notes_specifiques = htmlspecialchars(strip_tags($this->notes_specifiques));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":nom_complet", $this->nom_complet);
        $stmt->bindParam(":profession", $this->profession);
        $stmt->bindParam(":numero_telephone", $this->numero_telephone);
        $stmt->bindParam(":adresse_email", $this->adresse_email);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":entreprise_organisation", $this->entreprise_organisation);
        $stmt->bindParam(":date_naissance", $this->date_naissance);
        $stmt->bindParam(":tags_labels", $this->tags_labels);
        $stmt->bindParam(":notes_specifiques", $this->notes_specifiques);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete contact
    public function delete() {
        // Delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind id of record to delete
        $stmt->bindParam(1, $this->id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if email exists (useful for registration/contact uniqueness)
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE adresse_email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $this->adresse_email = htmlspecialchars(strip_tags($this->adresse_email));
        $stmt->bindParam(1, $this->adresse_email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return true;
        }
        return false;
    }
}
?>