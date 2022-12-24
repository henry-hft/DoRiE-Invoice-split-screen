<?php
include_once "./config/core.php";
include_once "./config/database.php";

include_once "./objects/Response.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
	Response::json(true, 400, "Invalid request method", true);
}

// instantiate database and server object
$database = new Database();
$db = $database->getConnection();

// get oldest event
$query = "SELECT id, seat, image, text, duration FROM events ORDER BY id ASC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();

$stmt->bindColumn("id", $id);
$stmt->bindColumn("seat", $seat);
$stmt->bindColumn("image", $image);
$stmt->bindColumn("text", $text);
$stmt->bindColumn("duration", $duration);

if ($stmt->fetch()) {
	$response = ["error" => false, "seat" => $seat, "image" => $image, "text" => $text, "duration" => (int) $duration * 1000];
} else {
	$response = ["error" => false, "image" => "", "text" => "", "duration" => 0];
}

// delete event
$query = "DELETE FROM events WHERE id=:id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $id);

if ($stmt->execute()) {
	// events successfully cleared
} else {
    Response::json(true, 400, "Could not delete event", true);
}

echo json_encode($response);
?>