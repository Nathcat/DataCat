<?php
include("start-session.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Accept: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (array_key_exists("user", $_SESSION) && array_key_exists("password", $request)) {
    $conn = new mysqli("localhost:3306", "sso", "", "SSO");
    $stmt = $conn->prepare("UPDATE Users SET password = ? WHERE id = ?");
    $stmt->bind_param("sd", password_hash($_POST["password"], PASSWORD_DEFAULT), $_SESSION["user"]["id"]);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
else {
    die(json_encode([
        "status" => "fail",
        "message" => "Missing data"
    ]));
}

session_destroy();
echo json_encode([
    "status" => "success"
]);
?>