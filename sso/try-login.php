<?php
include("start-session.php");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Accept: application/json");

$request = json_decode(file_get_contents("php://input"), true);

$DB_server = "localhost:3306";
$DB_user = "sso";
$DB_pass = "";
$DB_schema = "SSO";
$conn = new mysqli($DB_server, $DB_user, $DB_pass, $DB_schema);

$quick_auth_failed = false;

if ($request === null) {
  exit(json_encode([
    "status" => "fail",
    "message" => "No request supplied!"
  ]));
}

if (array_key_exists("quick-auth-token", $request)) {
    // Attempt quick auth

    $stmt = $conn->prepare("SELECT Users.* FROM QuickAuth JOIN Users ON QuickAuth.id = Users.id WHERE tokenHash = SHA2(?, 256)");
    $stmt->bind_param("s", $request["quick-auth-token"]);
    $stmt->execute();
    $set = $stmt->get_result();

    $result = $set->fetch_assoc();
    if ($result !== NULL) {
        // If there is a result, send it to the client and end here
        $_SESSION["user"] = $result;
        die(json_encode([
            "status" => "success",
            "user" => $result
        ]));
    }
    else {
        $quick_auth_failed = true;
    }

    // Otherwise continue...
}

if (array_key_exists("username", $request) && array_key_exists("password", $request)) {
    $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ?");
    $stmt->bind_param("s", $request["username"]);
    $stmt->execute();
    $set = $stmt->get_result();
    
    $result = $set->fetch_assoc();

    if ($result === NULL || !password_verify($request["password"], $result["password"])) {
        die(json_encode([
            "status" => "fail",
            "message" => "Invalid username / password combination."
        ]));
    }
    else {
        $_SESSION["user"] = $result;
        die(json_encode([
            "status" => "success",
            "user" => $result
        ]));
    }
}
else if ($quick_auth_failed) {
    die(json_encode([
        "status" => "fail",
        "message" => "Invalid quick auth token."
    ]));
}
else {
    die(json_encode([
        "status" => "fail",
        "message" => "Missing required fields!"
    ]));
}
?>
