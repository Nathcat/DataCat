<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
if (array_key_exists("HTTP_ORIGIN", $_SERVER)) header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$r = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("groupId", $r) || !array_key_exists("score", $r)) {
    exit(json_encode([
        "status" => "fail",
        "message" => "Missing some required fields!"
    ]));
}

$conn = new mysqli("localhost:3306", "study", "", "StudyCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {    
    $week = floor(time() / 604800);
    $stmt = $conn->prepare("INSERT INTO `pastquizscores` (`group`, `user`, `score`, `week`) values (?, ?, ?, ?)");
    $stmt->bind_param("iidi", $r["groupId"], $_SESSION["user"]["id"], $r["score"], $week);
    $stmt->execute();

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$stmt->close();

echo json_encode([
    "status" => "success"
]);

$conn->close();

?>