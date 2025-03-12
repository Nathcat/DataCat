<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$r = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("id", $r)) {
    exit(json_encode([
        "status" => "fail",
        "message" => "Please specify the id of the group."
    ]));
}

$conn = new mysqli("localhost:3306", "study", "", "StudyCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    ;

    $stmt = $conn->prepare("SELECT `name`, SSO.Users.id AS 'ownerId', SSO.Users.username AS 'ownerUsername', SSO.Users.fullName AS 'ownerFullName', SSO.Users.pfpPath AS 'ownerPfpPath' FROM `groups` JOIN SSO.Users ON SSO.Users.id = `groups`.`owner` WHERE `groups`.`id` = ?");
    $stmt->bind_param("i", $r["id"]);
    $stmt->execute();
} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$results = [
    "status" => "success",
    "group" => $stmt->get_result()->fetch_assoc()
];

$stmt->close();

try {
    ;

    $stmt = $conn->prepare("SELECT SSO.Users.id AS 'id', SSO.Users.username AS 'username', SSO.Users.fullName AS 'fullName', SSO.Users.pfpPath AS 'pfpPath', `admin` FROM `groupmembers` JOIN SSO.Users ON `user` = SSO.Users.id WHERE `group` = ?");
    $stmt->bind_param("i", $r["id"]);
    $stmt->execute();

    $members = [];
    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        array_push($members, $row);
    }

    $stmt->close();

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$results["members"] = $members;

echo json_encode($results);

$conn->close();

?>