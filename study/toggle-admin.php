<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$r = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("groupId", $r) || !array_key_exists("id", $r)) {
    exit(json_encode([
        "status" => "fail",
        "message" => "Missing required fields!"
    ]));
}

include("__determine-access-level.php");

$conn = new mysqli("localhost:3306", "study", "", "StudyCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}


if (!$_IS_ADMIN) {
    die(json_encode([
        "status" => "fail",
        "message" => "You are not an admin of this group!"
    ]));
}

try {
    $stmt = $conn->prepare("UPDATE `groupmembers` SET `admin` = b'1' - `admin` WHERE `group` = ? AND `user` = ?");
    $stmt->bind_param("ii", $r["groupId"], $r["id"]);
    $stmt->execute();

    $stmt->close();

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

echo json_encode([
    "status" => "success"
]);

$conn->close();

?>