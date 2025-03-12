<?php
include("../start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("apiKey", $request) || !array_key_exists("user", $request) || !array_key_exists("value", $request)) {
    die(json_encode([
        "status" => "fail",
        "message" => "Missing at least one required field! You must specify apiKey, user, and value!"
    ]));
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("SELECT id FROM Apps WHERE `apiKey` = ?");
    $stmt->bind_param("s", $request["apiKey"]);
    $stmt->execute();
    $count = 0;
    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        $count++;
    }

    if ($count == 0) {
        $conn->close();
        die(json_encode([
            "status" => "fail",
            "message" => "Invalid API key!"
        ]));
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

try {
    $stmt = $conn->prepare("INSERT INTO Leaderboards_Data (leaderboard, `user`, `value`) SELECT Leaderboards.id, ?, ? FROM Apps JOIN Leaderboards ON Leaderboards.app = Apps.id WHERE Apps.`apiKey` = ?");
    $stmt->bind_param("iis", $request["user"], $request["value"], $request["apiKey"]);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    }
    else {
        echo json_encode(["status" => "fail", "message" => $conn->error]);
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}


$conn->close();


?>