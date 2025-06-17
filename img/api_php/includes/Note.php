<?php
// api_php/includes/Note.php

class Note {
    private $conn;
    private $table_name = "notes";

    public $id;
    public $titre;
    public $sous_titre;
    public $contenu;
    public $dossiers;
    public $tags_labels;
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
            $this->titre = $row['titre'];
            $this->sous_titre = $row['sous_titre'];
            $this->contenu = $row['contenu'];
            $this->dossiers = $row['dossiers'];
            $this->tags_labels = $row['tags_labels'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    titre=:titre, sous_titre=:sous_titre, contenu=:contenu,
                    dossiers=:dossiers, tags_labels=:tags_labels";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->sous_titre = htmlspecialchars(strip_tags($this->sous_titre));
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->dossiers = htmlspecialchars(strip_tags($this->dossiers));
        $this->tags_labels = htmlspecialchars(strip_tags($this->tags_labels));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":sous_titre", $this->sous_titre);
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":dossiers", $this->dossiers);
        $stmt->bindParam(":tags_labels", $this->tags_labels);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    titre=:titre,
                    sous_titre=:sous_titre,
                    contenu=:contenu,
                    dossiers=:dossiers,
                    tags_labels=:tags_labels,
                    updated_at=CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->sous_titre = htmlspecialchars(strip_tags($this->sous_titre));
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->dossiers = htmlspecialchars(strip_tags($this->dossiers));
        $this->tags_labels = htmlspecialchars(strip_tags($this->tags_labels));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":sous_titre", $this->sous_titre);
        $stmt->bindParam(":contenu", $this->contenu);
        $stmt->bindParam(":dossiers", $this->dossiers);
        $stmt->bindParam(":tags_labels", $this->tags_labels);
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