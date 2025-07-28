<?php
include("../start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("id", $request)) {
    die(json_encode([
        "status" => "fail",
        "message" => "You must specify the id of the webhook!"
    ]));
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("SELECT id FROM Apps WHERE `owner` = ? AND id = ?");
    $stmt->bind_param("is", $_SESSION["user"]["id"], $request["app"]);
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
            "message" => "You do not own this app!"
        ]));
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

try {
    $stmt = $conn->prepare("DELETE FROM Leaderboards_Webhooks WHERE id = ?");
    $stmt->bind_param("i", $request["id"]);
    
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