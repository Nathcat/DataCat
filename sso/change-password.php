<?php
include("start-session.php");

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

if (array_key_exists("user", $_SESSION) && array_key_exists("password", $_POST)) {
    $conn = new mysqli("localhost:3306", "sso", "", "SSO");
    $stmt = $conn->prepare("UPDATE Users SET password = ?, passwordUpdated = 1 WHERE id = ?");
    $stmt->bind_param("sd", password_hash($_POST["password"], PASSWORD_DEFAULT), $_SESSION["user"]["id"]);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
else {
    die("Missing data!");
}

session_destroy();
?>