<?php
// api_php/api/tasks.php

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Task.php';

// Instantiate database and task object
$database = new Database();
$db = $database->getConnection();

$task = new Task($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Read single task
            $task->id = $id;
            if ($task->readOne()) {
                http_response_code(200);
                echo json_encode(array(
                    "id" => $task->id,
                    "titre_tache" => $task->titre_tache,
                    "date_heure_debut" => $task->date_heure_debut,
                    "date_heure_fin" => $task->date_heure_fin,
                    "details_description" => $task->details_description,
                    "priorite" => $task->priorite,
                    "statut" => $task->statut,
                    "created_at" => $task->created_at,
                    "updated_at" => $task->updated_at
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Task not found."));
            }
        } else {
            // Read all tasks
            $stmt = $task->read();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $tasks_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $task_item = array(
                        "id" => $id,
                        "titre_tache" => $titre_tache,
                        "date_heure_debut" => $date_heure_debut,
                        "date_heure_fin" => $date_heure_fin,
                        "details_description" => $details_description,
                        "priorite" => $priorite,
                        "statut" => $statut,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    array_push($tasks_arr, $task_item);
                }
                http_response_code(200);
                echo json_encode($tasks_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No tasks found."));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->titre_tache) &&
            !empty($data->date_heure_debut)
        ) {
            $task->titre_tache = $data->titre_tache;
            $task->date_heure_debut = $data->date_heure_debut;
            $task->date_heure_fin = isset($data->date_heure_fin) ? $data->date_heure_fin : null;
            $task->details_description = isset($data->details_description) ? $data->details_description : null;
            $task->priorite = isset($data->priorite) ? $data->priorite : null;
            $task->statut = isset($data->statut) ? $data->statut : null;

            if ($task->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Task was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create task."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create task. Data is incomplete."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($id) &&
            !empty($data->titre_tache) &&
            !empty($data->date_heure_debut)
        ) {
            $task->id = $id;
            $task->titre_tache = $data->titre_tache;
            $task->date_heure_debut = $data->date_heure_debut;
            $task->date_heure_fin = isset($data->date_heure_fin) ? $data->date_heure_fin : null;
            $task->details_description = isset($data->details_description) ? $data->details_description : null;
            $task->priorite = isset($data->priorite) ? $data->priorite : null;
            $task->statut = isset($data->statut) ? $data->statut : null;

            if ($task->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Task was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update task."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update task. Data or ID is incomplete."));
        }
        break;

    case 'DELETE':
        if (!empty($id)) {
            $task->id = $id;
            if ($task->delete()) {
                http_response_code(204);
                echo json_encode(array("message" => "Task was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete task."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete task. ID is missing."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>