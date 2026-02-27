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
    $stmt = $conn->prepare("SELECT * FROM Leaderboards JOIN Apps ON Apps.id = Leaderboards.app WHERE Apps.apiKey = ? AND Leaderboards.id = ?");
    $stmt->bind_param("si", $request["apiKey"], $request["leaderboardId"]);
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
            "message" => "Invalid API key!"
        ]));
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$success = false;
$added = true;

try {
    $stmt = $conn->prepare("SELECT * FROM Leaderboards_Data WHERE leaderboard = ? AND user = ?");
    $stmt->bind_param("ii", $request["leaderboardId"], $request["user"]);
    
    if ($stmt->execute()) {
        mysqli_report(MYSQLI_REPORT_ALL);

        $set = $stmt->get_result();
        $count = 0;
        $value = 0;
        while ($r = $set->fetch_assoc()) {
            $count++;
            $value = $r["value"];
        }

        if ($count === 0) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO Leaderboards_Data (leaderboard, user, `value`) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $request["leaderboardId"], $request["user"], $request["value"]);

            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success"
                ]);

                $success = true;
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

            if (array_key_exists("op", $request)) {
                if ($request["op"] == "-") { $request["value"] = -1 * $request["value"]; $added = false; }
            }

            $stmt = $conn->prepare("UPDATE Leaderboards_Data SET `value` = ? WHERE leaderboard = ? AND user = ?");
            $v = $value + $request["value"];
            $stmt->bind_param("iii", $v, $request["leaderboardId"], $request["user"]);

            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success"
                ]);

                $success = true;
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


if ($success) {
    try {
        $stmt = $conn->prepare("SELECT username FROM SSO.Users WHERE id = ?");
        $stmt->bind_param("i", $request["user"]);
        $stmt->execute();
        $username = $stmt->get_result()->fetch_assoc()["username"];

        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM Leaderboards WHERE id = ?");
        $stmt->bind_param("i", $request["leaderboardId"]);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $leaderboard_name = $result["name"];
        $leaderboard_id = $result["id"];
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->close();
        exit(0);
    }

    $data = array(
        //"content" => $username . " has " . ($added ? "received " : "lost ") . $request["value"] . " points!\n[View _" . $leaderboard_name . "_](https://data.nathcat.net/data/leaderboard/?id=" . $leaderboard_id . ")",
        "username" => $leaderboard_name,
        "avatar_url" => "https://cdn.nathcat.net/cloud/116bc634-b69a-11ef-9adc-067048c6a237.png",
        "embeds" => array(
            array(
                "color" => "#deddda",
                "title" => $leaderboard_name,
                "url" => "https://data.nathcat.net/data/leaderboard/?id=" . $leaderboard_id,
                "author" => array(
                    "name" => "DataCat",
                    "icon_url" => "https://cdn.nathcat.net/cloud/116bc634-b69a-11ef-9adc-067048c6a237.png",
                    "url" => "https://data.nathcat.net"
                ),
                "description" => $username . " has " . ($added ? "received " : "lost ") . $request["value"] . " points!",
                "thumbnail" => array(
                    "url" => "https://cdn.nathcat.net/cloud/116bc634-b69a-11ef-9adc-067048c6a237.png"
                ),
                "footer" => array (
                    "text" => "DataCat © Nathcat 2024 - " . date("Y"),
                    "icon_url" => "https://cdn.nathcat.net/cloud/116bc634-b69a-11ef-9adc-067048c6a237.png"
                )
            )
        )
    );

    echo json_encode($data);

    $options = array(
      'http' => array(
        'method'  => 'POST',
        'content' => json_encode( $data ),
        'header'=>  "Content-Type: application/json"
        )
    );

    $context  = stream_context_create( $options );

    try {
        $stmt = $conn->prepare("SELECT * FROM Leaderboards_Webhooks WHERE leaderboard = ?");
        $stmt->bind_param("i", $request["leaderboardId"]);
        $stmt->execute();
        $set = $stmt->get_result();

        while ($row = $set->fetch_assoc()) {
            file_get_contents( $row["url"], false, $context );
        }

    } catch (Exception $e) {
        $conn->close();
        exit(0);
    }
}

$conn->close();


?>
