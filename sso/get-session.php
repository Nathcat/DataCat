<?php
include("start-session.php");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://nathcat.net:8080");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

echo json_encode($_SESSION);
?>