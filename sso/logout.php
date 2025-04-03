<?php
include("start-session.php"); 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

session_destroy(); 
?>