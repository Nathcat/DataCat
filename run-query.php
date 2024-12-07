<?php 
$origin = $_SERVER["SERVER_NAME"];
if ($origin === "localhost") $origin = "http://localhost/";
else $origin = "https://data.nathcat.net/";

header("Access-Control-Allow-Origin: $origin");  // Will only allow either localhost or data.nathcat.net
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Accept: text/plain");
header("Content-Type: application/json");

session_name("AuthCat-SSO");
session_start();

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$query = file_get_contents("php://input");

$mysql_username = $_SESSION["user"]["id"] . "_" . $_SESSION["user"]["username"];
$mysql_password = $_SESSION["user"]["password"];
$mysql_db = $mysql_username . "_db";

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = new mysqli("localhost:3306", $mysql_username, $mysql_password, $mysql_db);
    $conn->multi_query($query);
    $set = [];

    do {
        if ($result = $conn->store_result()) {
            $r = $result->fetch_all(MYSQLI_ASSOC);
            array_push($set, $r);
            $result->free();
        }
    } 
    while ($conn->next_result());

    $conn->close();

    $out = [
        "status" => "success",
        "results" => $set
    ];

    echo json_encode($out);
}
catch (Exception $e) {
    die("{\"status\": \"error\", \"message\": \"" . str_replace("\\", "\\\\", str_replace("\n", "\\n", $e)) . "\"}");
}
?>