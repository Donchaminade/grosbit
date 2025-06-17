<?php
// api_php/api/credentials.php

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Credential.php';

// Instantiate database and credential object
$database = new Database();
$db = $database->getConnection();

$credential = new Credential($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Read single credential
            $credential->id = $id;
            if ($credential->readOne()) {
                http_response_code(200);
                // ATTENTION: Ne retournez JAMAIS le mot_de_passe_chiffre (le hash) au client !
                echo json_encode(array(
                    "id" => $credential->id,
                    "nom_site_compte" => $credential->nom_site_compte,
                    "nom_utilisateur_email" => $credential->nom_utilisateur_email,
                    // "mot_de_passe_chiffre" => $credential->mot_de_passe_chiffre, // NE PAS RETOURNER
                    "autres_infos_chiffre" => $credential->autres_infos_chiffre, // Ceci est déchiffré par readOne()
                    "categorie" => $credential->categorie,
                    "created_at" => $credential->created_at,
                    "updated_at" => $credential->updated_at
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Credential not found."));
            }
        } else {
            // Read all credentials
            $stmt = $credential->read(); // Cette méthode exclut déjà le champ mot_de_passe_chiffre
            $num = $stmt->rowCount();

            if ($num > 0) {
                $credentials_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $credential_item = array(
                        "id" => $id,
                        "nom_site_compte" => $nom_site_compte,
                        "nom_utilisateur_email" => $nom_utilisateur_email,
                        "autres_infos_chiffre" => $credential->decrypt($autres_infos_chiffre), // Déchiffrer ici pour chaque élément
                        "categorie" => $categorie,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    array_push($credentials_arr, $credential_item);
                }
                http_response_code(200);
                echo json_encode($credentials_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No credentials found."));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->nom_site_compte) &&
            !empty($data->nom_utilisateur_email) &&
            !empty($data->mot_de_passe_chiffre) // Le mot de passe clair est attendu pour la création
        ) {
            $credential->nom_site_compte = $data->nom_site_compte;
            $credential->nom_utilisateur_email = $data->nom_utilisateur_email;
            $credential->mot_de_passe_chiffre = $data->mot_de_passe_chiffre; // Le mot de passe clair sera haché par la classe
            $credential->autres_infos_chiffre = isset($data->autres_infos_chiffre) ? $data->autres_infos_chiffre : null; // Sera chiffré par la classe
            $credential->categorie = isset($data->categorie) ? $data->categorie : null;

            if ($credential->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Credential was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create credential."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create credential. Data is incomplete."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($id) &&
            !empty($data->nom_site_compte) &&
            !empty($data->nom_utilisateur_email)
            // mot_de_passe_chiffre n'est pas obligatoire pour la mise à jour
        ) {
            $credential->id = $id;
            $credential->nom_site_compte = $data->nom_site_compte;
            $credential->nom_utilisateur_email = $data->nom_utilisateur_email;
            // Si le mot de passe est fourni, mettez-le à jour (il sera haché)
            $credential->mot_de_passe_chiffre = isset($data->mot_de_passe_chiffre) ? $data->mot_de_passe_chiffre : null;
            $credential->autres_infos_chiffre = isset($data->autres_infos_chiffre) ? $data->autres_infos_chiffre : null; // Sera chiffré par la classe
            $credential->categorie = isset($data->categorie) ? $data->categorie : null;

            if ($credential->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Credential was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update credential."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update credential. Data or ID is incomplete."));
        }
        break;

    case 'DELETE':
        if (!empty($id)) {
            $credential->id = $id;
            if ($credential->delete()) {
                http_response_code(204);
                echo json_encode(array("message" => "Credential was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete credential."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete credential. ID is missing."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>