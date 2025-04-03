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

if (!array_key_exists("groupId", $r)) {
    exit(json_encode([
        "status" => "fail",
        "message" => "Please specify the id of the group."
    ]));
}

include("__determine-access-level.php");

echo json_encode([
    "status" => "success",
    "isAdmin" => $_IS_ADMIN
]);

?>