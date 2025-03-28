<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

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

if (array_key_exists("limit", $request)) {
    $LIMIT = $request["limit"];
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

if (array_key_exists("leaderboardId", $request)) {
    try {
        mysqli_report(MYSQLI_REPORT_ALL);
        $stmt = $conn->prepare("SELECT SSO.Users.username AS 'username', SSO.Users.fullName AS 'fullName', SSO.Users.pfpPath AS 'pfpPath', `value` FROM Leaderboards_Data JOIN SSO.Users ON `user` = SSO.Users.id WHERE leaderboard = ? ORDER BY `value` " . $request["orderBy"] . (defined($LIMIT) ? " LIMIT ?" : ""));
        if (defined($LIMIT)) $stmt->bind_param("ii", $request["leaderboardId"], $request["limit"]);
        else $stmt->bind_param("i", $request["orderBy"]);
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
        $stmt = $conn->prepare("SELECT SSO.Users.username AS 'username', SSO.Users.fullName AS 'fullName', SSO.Users.pfpPath AS 'pfpPath', `value` FROM Leaderboards_Data JOIN SSO.Users ON `user` = SSO.Users.id JOIN Leaderboards ON leaderboard = Leaderboards.id WHERE Leaderboards.`name` = ? ORDER BY `value` " . $request["orderBy"] . (defined($LIMIT) ? " LIMIT ?" : ""));
        if (defined($LIMIT)) $stmt->bind_param("si", $request["leaderboardName"], $request["limit"]);
        else $stmt->bind_param("s", $request["leaderboardName"]);
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