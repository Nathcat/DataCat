<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
header("Accept: application/json");
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$r = json_decode(file_get_contents("php://input"), true);

if (!array_key_exists("id", $r)) {
    exit(json_encode([
        "status" => "fail",
        "message" => "Please specify the id of the group."
    ]));
}

$conn = new mysqli("localhost:3306", "study", "", "StudyCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $week = floor(time() / 604800) - 1;
    $stmt = $conn->prepare("SELECT * FROM `questions` WHERE `week` = ? AND `group` = ?");
    $stmt->bind_param("ii", $week, $r["id"]);
    $stmt->execute();

    $questions = [];
    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        array_push($questions, $row);
    }

    $stmt->close();

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

for ($i = 0; $i < count($questions); $i++) {
    try {
        $stmt = $conn->prepare("SELECT `index`, `content` FROM `multiplechoiceoptions` WHERE `group` = ? AND `submittedBy` = ? AND `questionId` = ? ORDER BY `index` ASC");
        $stmt->bind_param("iis", $r["id"], $questions[$i]["submittedBy"], $questions[$i]["id"]);
        $stmt->execute();
    
        $mcqOptions = [];
        $set = $stmt->get_result();
        while ($row = $set->fetch_assoc()) {
            array_push($mcqOptions, $row);
        }

        if (count($mcqOptions) !== 0) $questions[$i]["mcqOptions"] = $mcqOptions;

        $stmt->close();

    } catch (Exception $e) {
        $conn->close();
        die("{\"status\": \"fail\", \"message\": \"$e\"}");
    }
}

echo json_encode([
    "status" => "success",
    "questions" => $questions
]);

$conn->close();

?>