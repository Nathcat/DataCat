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

if (!(array_key_exists("username", $_POST) && array_key_exists("email", $_POST) && array_key_exists("password", $_POST) && array_key_exists("password2", $_POST) && array_key_exists("fullName", $_POST))) {
    die("{\"status\": \"fail\", \"message\": \"Invalid request.\"}");
}
else if (preg_match("/^\\s+$|^$/", $_POST["username"]) || preg_match("/^\\s+$|^$/", $_POST["email"]) || preg_match("/^\\s+\$|^$/", $_POST["password"]) || preg_match("/^\\s+$|^$/", $_POST["password2"]) || preg_match("/^\\s+$|^$/", $_POST["fullName"])) {
    die("{\"status\": \"fail\", \"message\": \"Please do not leave any fields blank.\"}");
}
else if ($_POST["password"] != $_POST["password2"]) {
    die("{\"status\": \"fail\", \"message\": \"Passwords don't match.\"}");
}

$DB_server = "localhost:3306";
$DB_user = "sso";
$DB_pass = "";
$DB_schema = "SSO";

$conn = new mysqli($DB_server, $DB_user, $DB_pass, $DB_schema);

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

$hashed_pass = password_hash($_POST["password"], PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO Users (username, email, fullName, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $_POST["username"], $_POST["email"], $_POST["fullName"], $hashed_pass);
try {
    $stmt->execute();
    $stmt->close();
}
catch (Exception $e){
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"The username " . $_POST["username"] . " is already in use.\"}");
}

try {
    $stmt = $conn->prepare("SELECT id FROM Users WHERE username like ?");
    $stmt->bind_param("s", $_POST["username"]);
    $stmt->execute();
    $res = $stmt->get_result();
    $id = $res->fetch_assoc()["id"];
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO VerifyCodes (id, code) VALUES (?, LEFT(UUID(), 10))");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT code FROM VerifyCodes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute(); $res = $stmt->get_result();
    $code = $res->fetch_assoc()["code"];
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO Mailer.MailToSend (recipient, subject, content) VALUES (?, \"Welcome!\", \"<p>Dear \$fullName\$,</p><p>Welcome to the Nathcat network!</p><p>Your verification code is $code</p><p>Best wishes,<br>Nathan.</p>\")");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo "{\"status\": \"success\"}";
}
catch (Exception $e) {
    echo "{\"status\": \"fail\", \"message\": \"User was created but failed to create new user email notification: " . $e->getMessage() . "\"}";
}

$conn->close();
?>