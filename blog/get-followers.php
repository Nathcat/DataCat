<?php
include("../sso/start-session.php");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
$stmt = $conn->prepare("SELECT count(*) AS 'followers' FROM followers WHERE `follows` = ?");
$stmt->bind_param("i", $_SESSION["user"]["id"]);
$stmt->execute(); $res = $stmt->get_result()->fetch_assoc();

$stmt->close();
$conn->close();
echo json_encode($res);
?>
