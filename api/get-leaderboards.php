<?php
include("../start-session.php");

header("Content-Type: application/json");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

if ($request === null) $request = [];

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

if (array_key_exists("appId", $request)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM Leaderboards WHERE app = ?;");
        $stmt->bind_param("i", $request["appId"]);
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
}
else if (array_key_exists("appName", $request)) {
    try {
        $stmt = $conn->prepare("SELECT Leaderboards.* FROM Apps JOIN Leaderboards ON Apps.id = Leaderboards.app WHERE Apps.name = ?");
        $stmt->bind_param("s", $request["appName"]);
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
}
else {
    try {
        $stmt = $conn->prepare("SELECT Leaderboards.*, Apps.`name` AS 'appName' FROM Apps JOIN Leaderboards ON Leaderboards.app = Apps.id WHERE Apps.`owner` = ?");
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
}

$conn->close();

?>