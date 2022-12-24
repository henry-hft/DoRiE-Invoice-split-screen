<?php
class Response {
  public static function json(bool $error, int $responseCode, String $message, bool $exit): void {
    $response = ["error" => $error, "message" => $message];
	http_response_code($responseCode);
	if(!$exit){
		echo json_encode($response);
	} else {
		exit(json_encode($response));
	}
  }
}
?>