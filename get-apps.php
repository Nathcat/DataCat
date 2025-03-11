<?php
include("start-session.php");

header("Content-Type: application/json");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("SELECT * FROM Apps WHERE `owner` = ?;");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute();

    $r = array();
    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        array_push($r, $row);
    }

    echo json_encode([
        "status" => "success",
        "results" => $r
    ]);
} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}


$conn->close();

?>