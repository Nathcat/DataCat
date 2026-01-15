<?php

include("../start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");
header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("group", $request)) {
    die(json_encode(["status" => "fail", "message" => "You must specify the group ID."]));
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("SELECT count(*) AS 'count' FROM `Groups` WHERE owner = ? AND id = ?");
    $stmt->bind_param("ii", $_SESSION["user"]["id"], $request["group"]);
    $stmt->execute();

    $res = $stmt->get_result()->fetch_assoc()["count"];
    if ($res != 1) {
        $stmt = $conn->prepare("SELECT count(*) AS 'count' FROM `Group_Members` WHERE `group` = ? AND `user` = ?");
        $stmt->bind_param("ii", $request["group"], $_SESSION["user"]["id"]);
        $stmt->execute();

        $res = $stmt->get_result()->fetch_assoc()["count"];
        if ($res != 1) {
            die(json_encode([
                "status" => "fail",
                "message" => "The group does not exist, or you are not a member of it."
            ]));
        }
    }

    $stmt->close();

    $stmt = $conn->prepare("SELECT SSO.Users.id, SSO.Users.username, SSO.Users.fullName, SSO.Users.pfpPath FROM `Group_Members` JOIN SSO.Users ON `user` = SSO.Users.id WHERE `group` = ?");
    $stmt->bind_param("i", $request["group"]);
    $stmt->execute();

    $users = [];
    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        array_push($users, $row);
    }

    $stmt->close();
    $stmt = $conn->prepare("SELECT SSO.Users.id, SSO.Users.username, SSO.Users.fullName, SSO.Users.pfpPath FROM `Groups` JOIN SSO.Users ON SSO.Users.id = `Groups`.`owner` WHERE `Groups`.id = ?");
    $stmt->bind_param("i", $request["group"]);
    $stmt->execute();

    $owner = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$conn->close();

echo json_encode([
    "status" => "success",
    "owner" => $owner,
    "members" => $users
]);
