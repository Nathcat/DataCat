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

$DB_server = "localhost:3306";
$DB_user = "sso";
$DB_pass = "";
$DB_schema = "SSO";

if (array_key_exists("quick-auth-token", $_POST)) {

    try {
        mysqli_report(MYSQLI_REPORT_ALL);
        $conn = new mysqli($DB_server, $DB_user, $DB_pass, $DB_schema);
        $stmt = $conn->prepare("SELECT Users.* FROM QuickAuth JOIN Users on Users.id = QuickAuth.id WHERE tokenHash = SHA2(?, 256)");
        $stmt->bind_param("s", $_POST["quick-auth-token"]);
        $stmt->execute(); $set = $stmt->get_result();

        $res = $set->fetch_assoc();
        if ($res !== NULL) {
            $_SESSION["user"] = $res;
            unset($_SESSION["login-error"]);
            $stmt->close();
            $conn->close();
            die("{\"status\": \"success\", \"user\": " . json_encode($res) . "}");
        }

        $stmt->close();
        $conn->close();
    }
    catch (Exception $e) {
        echo $e;
    }
}

if (!(array_key_exists("username", $_POST) && array_key_exists("password", $_POST))) {
    die("{\"status\": \"fail\", \"message\": \"Invalid request.\"}");
}

else if ($_POST["username"] == "" || $_POST["password"] == "") {
    $_SESSION["login-error"] = "Please provide both username and password";
    die("{\"status\": \"fail\", \"message\": \"Please provide both username and password.\"}");
}

if (array_key_exists("DEBUG", $_GET)) {
    echo "<p>Username: " . $_POST["username"] . "<br>Password: " . $_POST["password"] . "</p>"; 
}

$conn = new mysqli($DB_server, $DB_user, $DB_pass, $DB_schema);

if ($conn->connect_error) {
    echo "{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}";
    die();
}

$stmt = $conn->prepare("SELECT * FROM Users WHERE username LIKE ?");
$stmt->bind_param("s", $_POST["username"]);
$stmt->execute();
$result = $stmt->get_result();
$DB_r = $result->fetch_assoc();

if (!$DB_r["passwordUpdated"]) {
    $pass_hash = hash("sha256", $_POST["password"]);
    
    if ($DB_r["password"] == $pass_hash) {
        echo "{\"status\": \"success\", \"user\": " . json_encode($DB_r) . "}";
        $_SESSION["user"] = $DB_r;
        unset($_SESSION["login-error"]);
    }
    else {
        echo "{\"status\": \"fail\", \"message\": \"Incorrect username / password combination.\"}";
        $_SESSION["login-error"] = "Incorrect username / password";
    }
}
else if (password_verify($_POST["password"], $DB_r["password"])) {
    echo "{\"status\": \"success\", \"user\": " . json_encode($DB_r) . "}";
    $_SESSION["user"] = $DB_r;
    unset($_SESSION["login-error"]);
}
else {
    echo "{\"status\": \"fail\", \"message\": \"Incorrect username / password combination.\"}";
    $_SESSION["login-error"] = "Incorrect username / password";
}

$conn->close();
?>