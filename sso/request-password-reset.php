<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Accept: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("email", $request)) {
    die(json_encode([
        "status" => "fail",
        "message" => "Please specify the user email"
    ]));
}

$DB_server = "localhost:3306";
$DB_user = "sso";
$DB_pass = "";
$DB_schema = "SSO";

$conn = new mysqli($DB_server, $DB_user, $DB_pass, $DB_schema);
if ($conn->connect_error) {
    die(json_encode([
        "status" => "fail",
        "message" => "Internal error, failed to connect to DB"
    ]));
}

try {
    $stmt = $conn->prepare("CALL request_password_reset(?);");
    $stmt->bind_param("s", $request["email"]);
    
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success"
        ]);
    }
    else {
        die(json_encode([
            "status" => "fail",
            "message" => "Failed to execute query"
        ]));
    }

} catch (Exception $e) {
    die(json_encode([
        "status" => "fail",
        "message" => $e->getMessage()
    ]));
}

$stmt->close();
$conn->close();
?>