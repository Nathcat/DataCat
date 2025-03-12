<?php
include("../start-session.php");

header("Content-Type: application/json");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

if ($request === null) $request = [];

if (!array_key_exists("orderBy", $request)) {
    die(json_encode([
        "status" => "fail",
        "message" => "You must specify orderBy."
    ]));
}
else {
    if (strtolower($request["orderBy"]) !== "asc" && strtolower($request["orderBy"]) !== "desc") {
        die(json_encode([
            "status" => "fail",
            "message" => "orderBy must be either ASC or DESC."
        ]));
    }
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

if (array_key_exists("leaderboardId", $request)) {
    try {
        $stmt = $conn->prepare("SELECT SSO.Users.username AS 'username', SSO.Users.fullName AS 'fullName', SSO.Users.pfpPath AS 'pfpPath', `value` FROM Leaderboards_Data JOIN SSO.Users ON `user` = SSO.Users.id WHERE leaderboard = ? ORDER BY " + $request["orderBy"]);
        $stmt->bind_param("i", $request["leaderboardId"]);
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
else if (array_key_exists("leaderboardName", $request)) {
    try {
        $stmt = $conn->prepare("SELECT SSO.Users.username AS 'username', SSO.Users.fullName AS 'fullName', SSO.Users.pfpPath AS 'pfpPath', `value` FROM Leaderboards_Data JOIN SSO.Users ON `user` = SSO.Users.id JOIN Leaderboards ON leaderboard = Leaderboards.id WHERE Leaderboards.`name` = ? ORDER BY " + $request["orderBy"]);
        $stmt->bind_param("s", $request["leaderboardName"]);
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
    echo json_encode([
        "status" => "fail",
        "message" => "You must specify either leaderboardId, or leaderboardName!"
    ]);
}

$conn->close();

?>