<?php
// api_php/api/notes.php

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Note.php';

// Instantiate database and note object
$database = new Database();
$db = $database->getConnection();

$note = new Note($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Read single note
            $note->id = $id;
            if ($note->readOne()) {
                http_response_code(200);
                echo json_encode(array(
                    "id" => $note->id,
                    "titre" => $note->titre,
                    "sous_titre" => $note->sous_titre,
                    "contenu" => $note->contenu,
                    "dossiers" => $note->dossiers,
                    "tags_labels" => $note->tags_labels,
                    "created_at" => $note->created_at,
                    "updated_at" => $note->updated_at
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Note not found."));
            }
        } else {
            // Read all notes
            $stmt = $note->read();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $notes_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $note_item = array(
                        "id" => $id,
                        "titre" => $titre,
                        "sous_titre" => $sous_titre,
                        "contenu" => $contenu,
                        "dossiers" => $dossiers,
                        "tags_labels" => $tags_labels,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    array_push($notes_arr, $note_item);
                }
                http_response_code(200);
                echo json_encode($notes_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No notes found."));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->titre) &&
            !empty($data->contenu)
        ) {
            $note->titre = $data->titre;
            $note->sous_titre = isset($data->sous_titre) ? $data->sous_titre : null;
            $note->contenu = $data->contenu;
            $note->dossiers = isset($data->dossiers) ? $data->dossiers : null;
            $note->tags_labels = isset($data->tags_labels) ? $data->tags_labels : null;

            if ($note->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Note was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create note."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create note. Data is incomplete."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($id) &&
            !empty($data->titre) &&
            !empty($data->contenu)
        ) {
            $note->id = $id;
            $note->titre = $data->titre;
            $note->sous_titre = isset($data->sous_titre) ? $data->sous_titre : null;
            $note->contenu = $data->contenu;
            $note->dossiers = isset($data->dossiers) ? $data->dossiers : null;
            $note->tags_labels = isset($data->tags_labels) ? $data->tags_labels : null;

            if ($note->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Note was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update note."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update note. Data or ID is incomplete."));
        }
        break;

    case 'DELETE':
        if (!empty($id)) {
            $note->id = $id;
            if ($note->delete()) {
                http_response_code(204);
                echo json_encode(array("message" => "Note was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete note."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete note. ID is missing."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>