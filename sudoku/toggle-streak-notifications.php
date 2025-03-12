<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$conn = new mysqli("localhost:3306", "Sudoku", "", "Sudoku");
$stmt = $conn->prepare("UPDATE UserData SET emailStreakNotifications = b'1' - emailStreakNotifications WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user"]["id"]);
if ($stmt->execute()) {
    $res = [
        "status" => "success"
    ];
}
else {
    $res = [
        "status" => "fail",
        "message" => "SQL Error"
    ];
}

$stmt->close();
$conn->close();
echo json_encode($res);
?>
