<?php
// api_php/includes/Credential.php

class Credential {
    private $conn;
    private $table_name = "credentials";

    // Propriétés de l'objet
    public $id;
    public $nom_site_compte;
    public $nom_utilisateur_email;
    public $mot_de_passe_chiffre; // Stockera le HASH du mot de passe
    public $autres_infos_chiffre; // Stockera les données chiffrées
    public $categorie;
    public $created_at;
    public $updated_at;

    // Pour le chiffrement/déchiffrement
    // !!! ATTENTION EN PRODUCTION: Cette clé doit être chargée de manière sécurisée
    // (par ex. variables d'environnement, secrets manager) et NON codée en dur.
    // REMPLACEZ 'YOUR_SECURE_ENCRYPTION_KEY_32_BYTES_LONG' par une clé aléatoire et sécurisée de 32 octets (256 bits).
    // Exemple de génération : openssl_random_pseudo_bytes(32)
    private $encryption_key = 'votre_cle_secrete_de_32_octets_ici!'; // Changez cette clé en PROD !
    private $cipher_algo = 'aes-256-cbc';

    public function __construct($db) {
        $this->conn = $db;
        // Assurez-vous que la clé est de la bonne longueur pour l'algorithme choisi
        // Pour AES-256-CBC, la clé doit faire 32 octets
        if (mb_strlen($this->encryption_key, '8bit') !== 32) {
            // En production, vous devriez logger ceci ou gérer une erreur fatale
            error_log("Credential class: Encryption key is not 32 bytes long. Please generate a proper key.");
            // Pour l'exemple, nous allons la tronquer/étendre (PAS RECOMMANDÉ EN PROD)
            $this->encryption_key = str_pad(mb_substr($this->encryption_key, 0, 32, '8bit'), 32, "\0");
        }
    }

    // --- Méthodes d'aide pour le chiffrement/déchiffrement ---
    private function encrypt($data) {
        if (empty($data)) return null;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher_algo));
        $encrypted_data = openssl_encrypt($data, $this->cipher_algo, $this->encryption_key, 0, $iv);
        // Retourne le IV concaténé avec les données chiffrées (pour le déchiffrement)
        return base64_encode($iv . $encrypted_data);
    }

    private function decrypt($data) {
        if (empty($data)) return null;
        $decoded_data = base64_decode($data);
        $iv_length = openssl_cipher_iv_length($this->cipher_algo);
        $iv = substr($decoded_data, 0, $iv_length);
        $encrypted_data = substr($decoded_data, $iv_length);
        return openssl_decrypt($encrypted_data, $this->cipher_algo, $this->encryption_key, 0, $iv);
    }

    // Read all credentials (attention: mot_de_passe_chiffre ne doit jamais être retourné en clair)
    public function read() {
        // Ne sélectionnez PAS le mot_de_passe_chiffre et autres_infos_chiffre directement pour l'affichage général
        // Si vous avez besoin d'autres_infos_chiffre, il faut le déchiffrer APRÈS la récupération
        $query = "SELECT id, nom_site_compte, nom_utilisateur_email, autres_infos_chiffre, categorie, created_at, updated_at
                  FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single credential (pour la vérification ou la récupération d'infos chiffrées)
    public function readOne() {
        $query = "SELECT id, nom_site_compte, nom_utilisateur_email, mot_de_passe_chiffre, autres_infos_chiffre, categorie, created_at, updated_at
                  FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->nom_site_compte = $row['nom_site_compte'];
            $this->nom_utilisateur_email = $row['nom_utilisateur_email'];
            $this->mot_de_passe_chiffre = $row['mot_de_passe_chiffre']; // Ceci est le hash, pas le mot de passe clair
            $this->autres_infos_chiffre = $this->decrypt($row['autres_infos_chiffre']); // Déchiffre ici
            $this->categorie = $row['categorie'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Create credential
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    nom_site_compte=:nom_site_compte,
                    nom_utilisateur_email=:nom_utilisateur_email,
                    mot_de_passe_chiffre=:mot_de_passe_chiffre,
                    autres_infos_chiffre=:autres_infos_chiffre,
                    categorie=:categorie";

        $stmt = $this->conn->prepare($query);

        // Sanitize and process data for storage
        $this->nom_site_compte = htmlspecialchars(strip_tags($this->nom_site_compte));
        $this->nom_utilisateur_email = htmlspecialchars(strip_tags($this->nom_utilisateur_email));
        $this->categorie = htmlspecialchars(strip_tags($this->categorie));

        // Hachage du mot de passe (utiliser PASSWORD_BCRYPT par défaut)
        // Le mot de passe clair doit être passé à cette méthode, pas le hash
        $hashed_password = password_hash($this->mot_de_passe_chiffre, PASSWORD_DEFAULT);
        $encrypted_other_info = $this->encrypt($this->autres_infos_chiffre);

        // Bind values
        $stmt->bindParam(":nom_site_compte", $this->nom_site_compte);
        $stmt->bindParam(":nom_utilisateur_email", $this->nom_utilisateur_email);
        $stmt->bindParam(":mot_de_passe_chiffre", $hashed_password); // Stocke le HASH
        $stmt->bindParam(":autres_infos_chiffre", $encrypted_other_info); // Stocke les données chiffrées
        $stmt->bindParam(":categorie", $this->categorie);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update credential
    public function update() {
        // IMPORTANT: Lors de la mise à jour, si le mot de passe n'est pas fourni,
        // ne le mettez pas à jour pour ne pas écraser le hash existant.
        // Si un nouveau mot de passe est fourni, hachez-le.

        $query = "UPDATE " . $this->table_name . " SET
                    nom_site_compte=:nom_site_compte,
                    nom_utilisateur_email=:nom_utilisateur_email,";

        // Ajoutez le champ mot_de_passe_chiffre à la requête de mise à jour SEULEMENT s'il est fourni
        if (!empty($this->mot_de_passe_chiffre)) {
            $query .= " mot_de_passe_chiffre=:mot_de_passe_chiffre,";
        }
        $query .= " autres_infos_chiffre=:autres_infos_chiffre,
                    categorie=:categorie,
                    updated_at=CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and process data
        $this->nom_site_compte = htmlspecialchars(strip_tags($this->nom_site_compte));
        $this->nom_utilisateur_email = htmlspecialchars(strip_tags($this->nom_utilisateur_email));
        $this->categorie = htmlspecialchars(strip_tags($this->categorie));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $encrypted_other_info = $this->encrypt($this->autres_infos_chiffre);

        // Bind values
        $stmt->bindParam(":nom_site_compte", $this->nom_site_compte);
        $stmt->bindParam(":nom_utilisateur_email", $this->nom_utilisateur_email);
        $stmt->bindParam(":autres_infos_chiffre", $encrypted_other_info);
        $stmt->bindParam(":categorie", $this->categorie);
        $stmt->bindParam(":id", $this->id);

        // Bind hashed password only if it's being updated
        if (!empty($this->mot_de_passe_chiffre)) {
            $hashed_password = password_hash($this->mot_de_passe_chiffre, PASSWORD_DEFAULT);
            $stmt->bindParam(":mot_de_passe_chiffre", $hashed_password);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete credential
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

    // Méthode pour vérifier un mot de passe (si vous aviez une authentification)
    // Elle ne fait pas partie du CRUD mais est essentielle pour la sécurité des credentials
    public function verifyPassword($plain_password) {
        $query = "SELECT mot_de_passe_chiffre FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($plain_password, $row['mot_de_passe_chiffre'])) {
            return true;
        }
        return false;
    }
}
?>