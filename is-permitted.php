<?php
$conn = new mysqli("localhost:3306", "Data", "", "DataCat");
$stmt = $conn->prepare("INSERT IGNORE INTO UserData (id) VALUES (?)");
$stmt->bind_param("i", $_SESSION["user"]["id"]);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("SELECT is_permitted FROM UserData WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user"]["id"]);
$stmt->execute();
$__IS_PERMITTED__ = $stmt->get_result()->fetch_assoc()["is_permitted"];
$stmt->close();
$conn->close();
?>