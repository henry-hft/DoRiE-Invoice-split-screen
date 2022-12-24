<?php
include_once "./config/core.php";
include_once "./config/database.php";

include_once "./objects/Response.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
	Response::json(true, 400, "Invalid request method", true);
}

if (!empty($_GET["seat"])) {
	
	if (!is_numeric($_GET["seat"])) {
		Response::json(true, 400, "Invalid input for seat", true);
	}
	
	if ($_GET["seat"] > $availableSeats OR $_GET["seat"] < 1) {
		Response::json(true, 400, "Invalid seat number", true);
	}
	
	$all = false;
	
} else {
	$all = true;
}

$currentStatus = "active";
$newStatus = "canceled";

if($all){ 
	$seatQuery = "";
} else {
	$seatQuery = " AND seat=:seat";
}

// instantiate database and server object
$database = new Database();
$db = $database->getConnection();

// cancel all active orders
$query = "UPDATE invoices SET status=:newStatus WHERE status=:currentStatus $seatQuery";
$stmt = $db->prepare($query);
$stmt->bindParam(":newStatus", $newStatus);
$stmt->bindParam(":currentStatus", $currentStatus);

if(!$all){
	$stmt->bindParam(":seat", $_GET["seat"]);
}


if ($stmt->execute()) {
	// all active orders successfully closed
} else {
    Response::json(true, 400, "The invoices could not be reset", true);
}


// delete events
if(!$all){
	$query = "DELETE FROM events WHERE seat=:seat";
	$stmt->bindParam(":seat", $_GET["seat"]);
} else {
	$query = "DELETE FROM events";
}

$stmt = $db->prepare($query);

if ($stmt->execute()) {
	// all events successfully deleted
} else {
    Response::json(true, 400, "Could not delete events", true);
}

Response::json(false, 200, "Application successfully reseted", false);
?>