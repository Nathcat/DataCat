<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://sudoku.nathcat.net");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

if ($_SERVER["CONTENT_TYPE"] == "text/plain" || array_key_exists("DEBUG", $_GET)) {
    if (array_key_exists("DEBUG", $_GET)) {
        $_PUZZLE = "2 8 4 6 9 5 7 3 1\n6 9 3 1 2 7 8 5 4\n7 1 5 8 4 3 2 9 6\n8 3 1 7 6 2 5 4 9\n9 7 2 5 3 4 6 1 8\n4 5 6 9 8 1 3 7 2\n1 2 9 3 5 6 4 8 7\n5 6 8 4 7 9 1 2 3\n3 4 7 2 1 8 9 6 5";
    }
    else {
        $_PUZZLE = file_get_contents("php://input");
    }
}

if (!isset($_PUZZLE)) {
    die("{\"status\": \"fail\", \"message\": \"No puzzle data was supplied.\"}");
}

$conn = new mysqli("localhost:3306", "Sudoku", "", "Sudoku");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("UPDATE UserData SET currentPuzzle = ? WHERE id = ?");
    $stmt->bind_param("si", $_PUZZLE, $_SESSION["user"]["id"]);
    $stmt->execute();
}
catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$conn->close();
echo "{\"status\": \"success\"}";

?>
