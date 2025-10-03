<?php
include("../start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die(json_encode([
        "status" => "fail",
        "message" => "Invalid request."
    ]));
}

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("subject", $request) || !array_key_exists("content", $request) || !array_key_exists("group", $request)) {
    die(json_encode([
        "status" => "fail",
        "message" => "Must supply email content and group ID!"
    ]));   
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $stmt = $conn->prepare("select `user` from `Group_Members` join `Groups` on `Groups`.`id` = `Group_Members`.`group` where `group` = ? and `owner` = ?");
    $stmt->bind_param("ii", $request["group"], $_SESSION["user"]["id"]);
    $stmt->execute();
    $set = $stmt->get_result();

    $count = 0;

    while ($user = $set->fetch_assoc()) {
        $count++;

        $stmt2 = $conn->prepare("insert into `Mailer`.`MailToSend` (recipient, subject, content) values (?, ?, ?)");
        $stmt2->bind_param("iss", $user["user"], $request["subject"], $request["content"]);
        $stmt2->execute();
    }

    if ($count !== 0) {
        echo json_encode([
            "status" => "success"
        ]);
    }
    else {
        echo json_encode([
            "status" => "fail",
            "message" => "Either there are no users in this group, or you are not the owner of this group"
        ]);
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}


$conn->close();


?>