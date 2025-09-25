<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: " . $_SERVER["SERVER_PROTOCOL"] . "://" . $_SERVER["SERVER_NAME"]);
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

include("start-session.php");

if (array_key_exists("user", $_SESSION)) {
    $conn = new mysqli("localhost:3306", "sso", "", "SSO");
    $stmt = $conn->prepare("CALL create_quick_auth(?)");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute(); $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    die("{\"status\": \"success\", \"token\": \"" . $res["t"] . "\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

$DB_server = "localhost:3306";
$DB_user = "sso";
$DB_pass = "";
$DB_schema = "SSO";

if (!array_key_exists("username", $request) || !array_key_exists("password", $request)) {
    die("{\"status\": \"fail\", \"message\": \"Invalid request.\"}");
}

else if ($request["username"] == "" || $request["password"] == "") {
    $_SESSION["login-error"] = "Please provide both username and password";
    die("{\"status\": \"fail\", \"message\": \"Please provide both username and password.\"}");
}

$conn = new mysqli($DB_server, $DB_user, $DB_pass, $DB_schema);

if ($conn->connect_error) {
    echo "{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}";
    die();
}

$stmt = $conn->prepare("SELECT * FROM Users WHERE username LIKE ?");
$stmt->bind_param("s", $request["username"]);
$stmt->execute();
$result = $stmt->get_result();
$DB_r = $result->fetch_assoc();

if (password_verify($request["password"], $DB_r["password"])) {
    $stmt->close();
    $stmt = $conn->prepare("CALL create_quick_auth(?)");
    $stmt->bind_param("i", $DB_r["id"]);
    $stmt->execute(); $res = $stmt->get_result()->fetch_assoc();

    echo "{\"status\": \"success\", \"token\": \"" . $res["t"] . "\"}";
}
else {
    die("{\"status\": \"fail\", \"message\": \"Incorrect username / password combination.\"}");
}

$stmt->close();
$conn->close();
?>