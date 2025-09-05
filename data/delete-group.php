<?php
include("../start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("group", $request)) {
    die(json_encode([
        "status" => "fail",
        "message" => "You must specify the id of the group."
    ]));
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("DELETE FROM `Groups` WHERE `id` = ? AND `owner` = ?");
    $stmt->bind_param("ii", $request["group"], $_SESSION["user"]["id"]);
    $stmt->execute();

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$conn->close();

echo json_encode(["status" => "success"]);

?>