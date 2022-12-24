<?php
include_once "./config/core.php";
include_once "./config/database.php";

include_once "./objects/Response.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
	Response::json(true, 400, "Invalid request method", true);
}

if (empty($_GET["id"])) {
	Response::json(true, 400, "Missing id GET-Parameter", true);
}

if (!is_numeric($_GET["id"])) {
	Response::json(true, 400, "Invalid input for id", true);
}

if (empty($_GET["function"])) {
	Response::json(true, 400, "Missing function GET-Parameter", true);
}

if($_GET["function"] != "pay" AND $_GET["function"] != "cancel"){
	Response::json(true, 400, "Invalid/unknown function", true);
}

// instantiate database and server object
$database = new Database();
$db = $database->getConnection();

$invoiceId = $_GET["id"];
$invoiceIdFormatted = sprintf("%04d", $invoiceId); // Add leading zeros: 0001 (4 digits)

$query = "SELECT seat, paid, status FROM invoices WHERE id=:id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $invoiceId);
$stmt->execute();

$stmt->bindColumn("seat", $seat);
$stmt->bindColumn("paid", $paid);
$stmt->bindColumn("status", $status);

if (!$stmt->fetch()) {
	Response::json(true, 400, "Invoice not found", true);
}

if($_GET["function"] == "pay"){
	
	if($paid == 1){
		Response::json(true, 400, "Invoice already paid", true);
	}
	
	if($status == "canceled"){
		Response::json(true, 400, "Invoice already canceled", true);
	}
	
	$paid = 1;

	$query = "UPDATE invoices SET paid=:paid WHERE id=:id";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":paid", $paid);
	$stmt->bindParam(":id", $invoiceId);

	if ($stmt->execute()) {
		Response::json(false, 200, "Invoice successfully paid", false);
	} else {
		Response::json(true, 400, "The invoice could not be paid", true);
	}

	// add event
	$image = "images/pay.png";
	$text = "Invoice #$invoiceIdFormatted successfully paid";

	$query = "INSERT INTO events (seat, image, text, duration) VALUES (:seat, :image, :text, :duration)";
	$stmt = $db->prepare($query);
	stmt->bindParam(":seat", $seat);
	$stmt->bindParam(":image", $image);
	$stmt->bindParam(":text", $text);
	$stmt->bindParam(":duration", $cancelEvent);

	if ($stmt->execute()) {
   		// new event created
	} else {
    	Response::json(true, 400, "Could not create a new event", true);
	}
}

if($_GET["function"] == "cancel"){
	
	if($paid == 1){
		Response::json(true, 400, "Invoice already paid", true);
	}
	
	if($status == "canceled"){
		Response::json(true, 400, "Invoice already canceled", true);
	}
	
	$newStatus = "canceled";

	$query = "UPDATE invoices SET status=:newStatus WHERE id=:id";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":newStatus", $newStatus);
	$stmt->bindParam(":id", $invoiceId);

	if ($stmt->execute()) {
		Response::json(false, 200, "Invoice successfully canceled", false);
	} else {
		Response::json(true, 400, "The invoice could not be canceled", true);
	}

	// add event
	$image = "images/cancel.png";
	$text = "Invoice #$invoiceIdFormatted successfully canceled";
	
	$query = "INSERT INTO events (seat, image, text, duration) VALUES (:seat, :image, :text, :duration)";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":seat", $seat);
	$stmt->bindParam(":image", $image);
	$stmt->bindParam(":text", $text);
	$stmt->bindParam(":duration", $payEvent);
	
	if ($stmt->execute()) {
			// new event created
	} else {
		Response::json(true, 400, "Could not create a new event", true);
	}
}
?>
