<?php
include("../start-session.php");

header("Content-Type: application/json");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
}

$owned = array();
$member_of = array();

try {
    $stmt = $conn->prepare("SELECT `Groups`.*, SSO.Users.username AS 'ownerUsername' FROM `Groups` JOIN SSO.Users ON `Groups`.`owner` = SSO.Users.id WHERE `owner` = ?");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute();

    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        array_push($owned, $row);
    }

    $stmt->close();

    $stmt = $conn->prepare("SELECT `Groups`.*, SSO.Users.username AS 'ownerUsername' FROM `Group_Members` JOIN `Groups` ON `Group_Members`.`user` = ? JOIN SSO.Users ON `Groups`.`owner` = SSO.Users.id");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute();

    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        array_push($member_of, $row);
    }

    $stmt->close();

} catch (Exception $e) {
    $conn->close();
    die("{\"status\": \"fail\", \"message\": \"$e\"}");
}

$conn->close();

echo json_encode([
    "status" => "success",
    "owned" => $owned,
    "memberOf" => $member_of
]);

?>