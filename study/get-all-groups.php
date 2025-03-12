<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
if (array_key_exists("HTTP_ORIGIN", $_SERVER)) header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$conn = new mysqli("localhost:3306", "study", "", "StudyCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

$groups = [];
$owned_groups = [];

try {
    ;

    $stmt = $conn->prepare("SELECT `groups`.`id` AS 'id', `groups`.`owner` AS 'owner', `groups`.`name` AS 'name', `groupmembers`.`admin` FROM `groupmembers` JOIN `groups` ON `groupmembers`.`group` = `groups`.`id` WHERE `groupmembers`.`user` = ?");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute();
    $set = $stmt->get_result();

    while ($row = $set->fetch_assoc()) {
        array_push($groups, $row);
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

try {
    ;

    $stmt = $conn->prepare("SELECT `groups`.`id` AS 'id', `groups`.`owner` AS 'owner', `groups`.`name` AS 'name' FROM `groups` WHERE `groups`.`owner` = ?");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute();
    $set = $stmt->get_result();

    while ($row = $set->fetch_assoc()) {
        array_push($owned_groups, $row);
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$stmt->close();

echo json_encode([
    "status" => "success",
    "groups" => $groups,
    "ownedGroups" => $owned_groups
]);

$conn->close();

?>