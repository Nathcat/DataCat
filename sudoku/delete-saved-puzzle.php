<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://sudoku.nathcat.net");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$conn = new mysqli("localhost:3306", "Sudoku", "", "Sudoku");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("UPDATE UserData SET currentPuzzle = NULL WHERE id = ?");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute();
}
catch (Exception $e) {
    $stmt->close();
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$stmt->close();
$conn->close();
echo "{\"status\": \"success\"}";

?>
