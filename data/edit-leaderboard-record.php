<?php
header("Content-Type: application/json");
header("Accept: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

$request = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("apiKey", $request) || !array_key_exists("leaderboardId", $request) || !array_key_exists("user", $request) || !array_key_exists("value", $request)) {
    die(json_encode([
        "status" => "fail",
        "message" => "Missing at least one required field! You must specify apiKey, leaderboardId, user, and value!"
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
    $stmt = $conn->prepare("SELECT * FROM Leaderboards_Data WHERE leaderboard = ? AND user = ?");
    $stmt->bind_param("ii", $request["leaderboardId"], $request["user"]);
    
    if ($stmt->execute()) {
        mysqli_report(MYSQLI_REPORT_ALL);

        $set = $stmt->get_result();
        $count = 0;
        while ($r = $set->fetch_assoc()) {
            $count++;
        }

        echo $count;

        if ($count === 0) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO Leaderboards_Data (leaderboard, user, `value`) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $request["leaderboardId"], $request["user"], $request["value"]);

            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success"
                ]);
            }
            else {
                echo json_encode([
                    "status" => "fail",
                    "message" => $conn->error
                ]);
            }

            $stmt->close();
        }
        else {
            $stmt->close();
            $stmt = $conn->prepare("UPDATE Leaderboards_Data SET `value` = ? WHERE leaderboard = ? AND user = ?");
            $stmt->bind_param("iii", $request["value"], $request["leaderboardId"], $request["user"]);

            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success"
                ]);
            }
            else {
                echo json_encode([
                    "status" => "fail",
                    "message" => $conn->error
                ]);
            }

            $stmt->close();
        }
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