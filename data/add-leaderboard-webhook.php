<?php
include("../start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("name", $request) || !array_key_exists("url", $request) || !array_key_exists("leaderboard", $request) || !array_key_exists("apiKey", $request)) {
    die(json_encode([
        "status" => "fail",
        "message" => "You must specify the name, url, leaderboard, and API key related to the webhook and app!"
    ]));
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("SELECT * FROM Leaderboards JOIN Apps ON Apps.id = Leaderboards.app WHERE Apps.apiKey = ? AND Leaderboards.id = ?");
    $stmt->bind_param("si", $request["apiKey"], $request["leaderboard"]);
    $stmt->execute();
    $count = 0;
    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        $count++;
    }

    if ($count != 1) {
        $conn->close();
        die(json_encode([
            "status" => "fail",
            "message" => "Invalid API key, or leaderboard does not exist!"
        ]));
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

try {
    $stmt = $conn->prepare("INSERT INTO Leaderboards_Webhooks (`name`, `leaderboard`, `url`) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $request["name"], $request["leaderboard"], $request["url"]);
    
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