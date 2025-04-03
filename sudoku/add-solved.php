<?php
include("../sso/start-session.php");
include("sudoku-utils.php");

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
    $_PUZZLE = explode("\n", $_PUZZLE);

    // Process the puzzle string into a 2D array
    $_PUZZLE = array_map(function($v) {
        return array_map(function($x) {
            return intval($x) <= 0 ? 0 : intval($x);
        }, explode(" ", $v));
    }, $_PUZZLE);

    if (array_key_exists("DEBUG", $_GET)) {
        if (is_solved($_PUZZLE)) echo "solved";
        else echo "Not solved";
    }
}
else {
    die("{\"status\": \"fail\", \"message\": \"May only pass text/plain with body as Sudoku data.\"}");
}

if (!is_solved($_PUZZLE)) {
    die("{\"status\": \"fail\", \"message\": \"Submitted puzzle is not solved!\", \"puzzle\": \"" + "\"}");
}

$conn = new mysqli("localhost:3306", "Sudoku", "", "Sudoku");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

if (array_key_exists("DEBUG", $_GET)) {
    echo "Starting SQL.";
}

try {
    $stmt = $conn->prepare("INSERT INTO SolvedPuzzles (puzzle) VALUES (?)");
    $stmt->bind_param("s", implode("", array_map(function($v) {
        return implode("", $v);
    }, $_PUZZLE)));

    $stmt->execute(); $stmt->close();
}
catch (Exception $e) {
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

try {
    $stmt = $conn->prepare("INSERT INTO UserData (id) VALUES (?)");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute(); $stmt->close();
} catch (Exception $e) {
    if (array_key_exists("DEBUG", $_GET)) {
        echo $e;
    }
}

if (array_key_exists("DEBUG", $_GET)) {
    echo "Done first query.";
}

$stmt = $conn->prepare("UPDATE UserData SET puzzlesSolved = puzzlesSolved + 1, hasSolvedToday = 1 WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user"]["id"]);
$stmt->execute(); $stmt->close();

if (array_key_exists("DEBUG", $_GET)) {
    echo "Done second query.";
}

$conn->close();

echo "{\"status\": \"success\"}";
?>