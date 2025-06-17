<?php
// api_php/api/contacts.php

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Contact.php';

// Instantiate database and contact object
$database = new Database();
$db = $database->getConnection();

$contact = new Contact($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Read single contact
            $contact->id = $id;
            if ($contact->readOne()) {
                http_response_code(200);
                echo json_encode(array(
                    "id" => $contact->id,
                    "nom_complet" => $contact->nom_complet,
                    "profession" => $contact->profession,
                    "numero_telephone" => $contact->numero_telephone,
                    "adresse_email" => $contact->adresse_email,
                    "adresse" => $contact->adresse,
                    "entreprise_organisation" => $contact->entreprise_organisation,
                    "date_naissance" => $contact->date_naissance,
                    "tags_labels" => $contact->tags_labels,
                    "notes_specifiques" => $contact->notes_specifiques,
                    "created_at" => $contact->created_at,
                    "updated_at" => $contact->updated_at
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Contact not found."));
            }
        } else {
            // Read all contacts
            $stmt = $contact->read();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $contacts_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row); // Extract row to variables
                    $contact_item = array(
                        "id" => $id,
                        "nom_complet" => $nom_complet,
                        "profession" => $profession,
                        "numero_telephone" => $numero_telephone,
                        "adresse_email" => $adresse_email,
                        "adresse" => $adresse,
                        "entreprise_organisation" => $entreprise_organisation,
                        "date_naissance" => $date_naissance,
                        "tags_labels" => $tags_labels,
                        "notes_specifiques" => $notes_specifiques,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    array_push($contacts_arr, $contact_item);
                }
                http_response_code(200);
                echo json_encode($contacts_arr);
            } else {
                http_response_code(404); // Or 200 with empty array, depending on preference
                echo json_encode(array("message" => "No contacts found."));
            }
        }
        break;

    case 'POST':
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));

        // Make sure data is not empty
        if (
            !empty($data->nom_complet) &&
            !empty($data->adresse_email)
        ) {
            // Set contact property values
            $contact->nom_complet = $data->nom_complet;
            $contact->profession = isset($data->profession) ? $data->profession : null;
            $contact->numero_telephone = isset($data->numero_telephone) ? $data->numero_telephone : null;
            $contact->adresse_email = $data->adresse_email;
            $contact->adresse = isset($data->adresse) ? $data->adresse : null;
            $contact->entreprise_organisation = isset($data->entreprise_organisation) ? $data->entreprise_organisation : null;
            $contact->date_naissance = isset($data->date_naissance) ? $data->date_naissance : null;
            $contact->tags_labels = isset($data->tags_labels) ? $data->tags_labels : null;
            $contact->notes_specifiques = isset($data->notes_specifiques) ? $data->notes_specifiques : null;

            // Create the contact
            if ($contact->create()) {
                http_response_code(201); // Created
                echo json_encode(array("message" => "Contact was created."));
            } else {
                http_response_code(503); // Service unavailable
                echo json_encode(array("message" => "Unable to create contact."));
            }
        } else {
            http_response_code(400); // Bad request
            echo json_encode(array("message" => "Unable to create contact. Data is incomplete."));
        }
        break;

    case 'PUT':
        // Get raw data
        $data = json_decode(file_get_contents("php://input"));

        // Make sure ID and data are not empty
        if (
            !empty($id) &&
            !empty($data->nom_complet) &&
            !empty($data->adresse_email)
        ) {
            // Set ID property of contact to be edited
            $contact->id = $id;

            // Set contact property values
            $contact->nom_complet = $data->nom_complet;
            $contact->profession = isset($data->profession) ? $data->profession : null;
            $contact->numero_telephone = isset($data->numero_telephone) ? $data->numero_telephone : null;
            $contact->adresse_email = $data->adresse_email;
            $contact->adresse = isset($data->adresse) ? $data->adresse : null;
            $contact->entreprise_organisation = isset($data->entreprise_organisation) ? $data->entreprise_organisation : null;
            $contact->date_naissance = isset($data->date_naissance) ? $data->date_naissance : null;
            $contact->tags_labels = isset($data->tags_labels) ? $data->tags_labels : null;
            $contact->notes_specifiques = isset($data->notes_specifiques) ? $data->notes_specifiques : null;

            // Update the contact
            if ($contact->update()) {
                http_response_code(200); // OK
                echo json_encode(array("message" => "Contact was updated."));
            } else {
                http_response_code(503); // Service unavailable
                echo json_encode(array("message" => "Unable to update contact."));
            }
        } else {
            http_response_code(400); // Bad request
            echo json_encode(array("message" => "Unable to update contact. Data or ID is incomplete."));
        }
        break;

    case 'DELETE':
        // Make sure ID is not empty
        if (!empty($id)) {
            // Set contact ID to be deleted
            $contact->id = $id;

            // Delete the contact
            if ($contact->delete()) {
                http_response_code(204); // No Content
                echo json_encode(array("message" => "Contact was deleted."));
            } else {
                http_response_code(503); // Service unavailable
                echo json_encode(array("message" => "Unable to delete contact."));
            }
        } else {
            http_response_code(400); // Bad request
            echo json_encode(array("message" => "Unable to delete contact. ID is missing."));
        }
        break;

    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>