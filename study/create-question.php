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

if (!array_key_exists("groupId", $r) || !array_key_exists("content", $r) || !array_key_exists("mcqOrString", $r) || !array_key_exists("answer", $r)) {
    die(json_encode([
        "status" => "fail",
        "message" => "Missing required fields!"
    ]));
}

$isMCQ = strcasecmp($r["mcqOrString"], "mcq") == 0;
if ($isMCQ && !array_key_exists("mcqOptions", $r)) {
    die(json_encode([
        "status" => "fail",
        "message" => "Missing required fields!"
    ]));
}

include("__determine-access-level.php");

if (!$_IS_MEMBER) {
    die(json_encode([
        "status" => "fail",
        "message" => "You are not a member of this group!"
    ]));
}


$conn = new mysqli("localhost:3306", "study", "", "StudyCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

try {
    $week = floor(time() / 604800);
    $stmt = $conn->prepare("CALL create_question(?, ?, ?, " . ($isMCQ ? "?, null" : "null, ?") . ", ?)");
    $stmt->bind_param("iis" . ($isMCQ ? "i" : "s") . "i", $r["groupId"], $_SESSION["user"]["id"], $r["content"], $r["answer"], $week);
    $stmt->execute();
    $q_id = $stmt->get_result()->fetch_assoc()["id"];
    $stmt->close();

    if ($isMCQ) {
        for ($i = 0; $i < count($r["mcqOptions"]); $i++) {
            $stmt = $conn->prepare("INSERT INTO `multiplechoiceoptions` (`group`, `submittedBy`, `questionId`, `index`, `content`) values (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisis", $r["groupId"], $_SESSION["user"]["id"], $q_id, $i, $r["mcqOptions"][$i]);
            $stmt->execute();
            $stmt->close();
        }
    }

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

echo json_encode([
    "status" => "success"
]);

$conn->close();

?>