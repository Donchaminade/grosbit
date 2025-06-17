<?php
// api_php/includes/Task.php

class Task {
    private $conn;
    private $table_name = "tasks";

    public $id;
    public $titre_tache;
    public $date_heure_debut;
    public $date_heure_fin;
    public $details_description;
    public $priorite; // Enum: 'Haute','Moyenne','Basse'
    public $statut;   // Enum: 'À faire','En cours','Terminé','Annulé'
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->titre_tache = $row['titre_tache'];
            $this->date_heure_debut = $row['date_heure_debut'];
            $this->date_heure_fin = $row['date_heure_fin'];
            $this->details_description = $row['details_description'];
            $this->priorite = $row['priorite'];
            $this->statut = $row['statut'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    titre_tache=:titre_tache, date_heure_debut=:date_heure_debut,
                    date_heure_fin=:date_heure_fin, details_description=:details_description,
                    priorite=:priorite, statut=:statut";

        $stmt = $this->conn->prepare($query);

        $this->titre_tache = htmlspecialchars(strip_tags($this->titre_tache));
        $this->date_heure_debut = htmlspecialchars(strip_tags($this->date_heure_debut));
        $this->date_heure_fin = htmlspecialchars(strip_tags($this->date_heure_fin));
        $this->details_description = htmlspecialchars(strip_tags($this->details_description));
        $this->priorite = htmlspecialchars(strip_tags($this->priorite));
        $this->statut = htmlspecialchars(strip_tags($this->statut));

        $stmt->bindParam(":titre_tache", $this->titre_tache);
        $stmt->bindParam(":date_heure_debut", $this->date_heure_debut);
        $stmt->bindParam(":date_heure_fin", $this->date_heure_fin);
        $stmt->bindParam(":details_description", $this->details_description);
        $stmt->bindParam(":priorite", $this->priorite);
        $stmt->bindParam(":statut", $this->statut);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    titre_tache=:titre_tache,
                    date_heure_debut=:date_heure_debut,
                    date_heure_fin=:date_heure_fin,
                    details_description=:details_description,
                    priorite=:priorite,
                    statut=:statut,
                    updated_at=CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->titre_tache = htmlspecialchars(strip_tags($this->titre_tache));
        $this->date_heure_debut = htmlspecialchars(strip_tags($this->date_heure_debut));
        $this->date_heure_fin = htmlspecialchars(strip_tags($this->date_heure_fin));
        $this->details_description = htmlspecialchars(strip_tags($this->details_description));
        $this->priorite = htmlspecialchars(strip_tags($this->priorite));
        $this->statut = htmlspecialchars(strip_tags($this->statut));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":titre_tache", $this->titre_tache);
        $stmt->bindParam(":date_heure_debut", $this->date_heure_debut);
        $stmt->bindParam(":date_heure_fin", $this->date_heure_fin);
        $stmt->bindParam(":details_description", $this->details_description);
        $stmt->bindParam(":priorite", $this->priorite);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>