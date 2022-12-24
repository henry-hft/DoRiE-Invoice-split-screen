<?php
include_once "./config/core.php";
include_once "./config/database.php";

include_once "./objects/Response.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
	Response::json(true, 400, "Invalid request method", true);
}

if (empty($_GET["seat"])) {
	Response::json(true, 400, "Missing seat GET-Parameter", true);
}

if (!is_numeric($_GET["seat"])) {
	Response::json(true, 400, "Invalid input for seat", true);
}

if ($_GET["seat"] > $availableSeats OR $_GET["seat"] < 1) {
	Response::json(true, 400, "Invalid seat number", true);
}

// instantiate database and server object
$database = new Database();
$db = $database->getConnection();

$seat = $_GET["seat"];
$paid = 0;
$time = time() - $invoiceDuration;
$status = "active";
$requested = "0";

// get latest invoice
$query = "SELECT id FROM invoices WHERE seat=:seat AND paid=:paid AND time>=:time AND status=:status AND requested=:requested ORDER BY id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(":seat", $seat);
$stmt->bindParam(":paid", $paid);
$stmt->bindParam(":time", $time);
$stmt->bindParam(":status", $status);
$stmt->bindParam(":requested", $requested);
$stmt->execute();

$stmt->bindColumn("id", $invoiceId);
//$stmt->bindColumn("requested", $requested);

if ($stmt->fetch()) {
	
	// set invoice to payment mode

	$newStatus = "completed";
	$requested = time();

	$query = "UPDATE invoices SET status=:status, requested=:requested WHERE id=:id";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":status", $newStatus);
	$stmt->bindParam(":requested", $requested);
	$stmt->bindParam(":id", $invoiceId);

	if ($stmt->execute()) {
		// order status successfully set to completed
		$invoiceIdFormatted = sprintf("%04d", $invoiceId);
		$url = urlencode("$baseUrl/invoice.html?id=$invoiceId");
		$response = ["error" => false, "qrcode" => "$baseUrl/qrcode.php?url=$url"];
	} else {
		Response::json(true, 400, "Could not set invoice status to completed", true);
	}
	
} else {
	
	// check if there is an invoice in payment mode
	
	$statusCompleted = "completed";
	$statusCanceled = "canceled";
	$requestedTime = time() - $paymentModeDuration;
	
	$query = "SELECT id, status, paid FROM invoices WHERE seat=:seat AND time>=:time AND requested>=:requested AND (status=:statusCompleted OR status=:statusCanceled) ORDER BY id DESC LIMIT 1";
	$stmt = $db->prepare($query);
	$stmt->bindParam(":seat", $seat);
	$stmt->bindParam(":time", $time);
	$stmt->bindParam(":requested", $requestedTime);
	$stmt->bindParam(":statusCompleted", $statusCompleted);
	$stmt->bindParam(":statusCanceled", $statusCanceled);
	$stmt->execute();

	$stmt->bindColumn("id", $id);
	$stmt->bindColumn("status", $newStatus);
	$stmt->bindColumn("paid", $paidValue);

	if ($stmt->fetch()) {
		
		// close invoice
		if($newStatus == $statusCanceled OR $paidValue == 1){
			
			$newRequested = "-1";
		
			$query = "UPDATE invoices SET requested=:requested WHERE id=:id";
			$stmt = $db->prepare($query);
			$stmt->bindParam(":requested", $newRequested);
			$stmt->bindParam(":id", $id);

			if ($stmt->execute()) {
				// invoice successfully closed
			} else {
				Response::json(true, 400, "Could not close the inovice", true);
			}
		}
		
		if($newStatus == $statusCanceled){
			Response::json(true, 400, "The invoice for seat $seat was canceled", true);
		}
		
		if($paidValue == 0){
			Response::json(true, 400, "Active invoice in payment mode for seat $seat", true);
		} else {
			Response::json(true, 400, "The invoice for seat $seat was successfully paid", true);
		}
		
	} else {
		Response::json(true, 400, "No active invoice found for seat $seat", true);
	}
}


// update invoice

$newStatus = "completed";

$query = "UPDATE invoices SET status=:newStatus WHERE id=:id";
$stmt = $db->prepare($query);
$stmt->bindParam(":newStatus", $newStatus);
$stmt->bindParam(":id", $invoiceId);

if ($stmt->execute()) {
	// order status successfully set to completed
} else {
    Response::json(true, 400, "Could not set invoice status to completed", true);
}

// add event
$image = "$baseUrl/qrcode.php?url=$url";
$text = "Seat: $seat | Invoice: #$invoiceIdFormatted";

$query = "INSERT INTO events (seat, image, text, duration) VALUES (:seat, :image, :text, :duration)";
$stmt = $db->prepare($query);
$stmt->bindParam(":seat", $seat);
$stmt->bindParam(":image", $image);
$stmt->bindParam(":text", $text);
$stmt->bindParam(":duration", $qrCodeEvent);

if ($stmt->execute()) {
    // new event created
} else {
    Response::json(true, 400, "Could not create a new event", true);
}

echo json_encode($response);
?>