<?php
include("start-session.php");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["CONTENT_TYPE"] == "application/json") {
    $_POST = json_decode(file_get_contents("php://input"), true);
}

if (array_key_exists("DEBUG", $_GET)) {
    echo "<p>In debug mode.</p>";
    print_r($_POST);
}

if (array_key_exists("id", $_POST) && array_key_exists("token", $_POST)) {
    $conn = new mysqli("localhost:3306", "sso", "", "SSO");
    $stmt = $conn->prepare("delete from QuickAuth where id = ? and tokenHash = SHA2(?, 256)");
    $stmt->bind_param("is", $_POST["id"], $_POST["token"]);
    $stmt->execute();

    echo "{\"status\": \"success\"}";
}
else if (array_key_exists("id", $_POST)) {
    $conn = new mysqli("localhost:3306", "sso", "", "SSO");
    $stmt = $conn->prepare("delete from QuickAuth where id = ?");
    $stmt->bind_param("i", $_POST["id"]);
    $stmt->execute();

    echo "{\"status\": \"success\"}";
}
else {
    die("{\"status\": \"fail\", \"message\": \"Invalid request.\"}");
}
?>