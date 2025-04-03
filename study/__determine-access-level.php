<?php
$_IS_MEMBER = false;
$_IS_ADMIN = false;
$_IS_OWNER = false;
$__conn = new mysqli("localhost:3306", "study", "", "StudyCat");

if ($__conn->connect_error) {
    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $__conn->connect_error . "\"}");
}

try {
    $__stmt = $__conn->prepare("SELECT `owner` FROM `groups` WHERE `id` = ?");
    $__stmt->bind_param("i", $r["groupId"]);
    $__stmt->execute();

    $owner = $__stmt->get_result()->fetch_assoc()["owner"];
    if ($owner == $_SESSION["user"]["id"]) $_IS_OWNER = true;

    $__stmt->close();

} catch (Exception $e) {
    $__conn->close();
}

try {
    $__stmt = $__conn->prepare("SELECT `user`, `admin` FROM `groupmembers` JOIN SSO.Users ON `user` = SSO.Users.id WHERE `group` = ? AND `user` = ?");
    $__stmt->bind_param("ii", $r["groupId"], $_SESSION["user"]["id"]);
    $__stmt->execute();

    $set = $__stmt->get_result();
    if ($row = $set->fetch_assoc()) { 
        $_IS_MEMBER = true; 
        if ($row["admin"] == 1) {
            $_IS_ADMIN = true;
        }
    }

    $__stmt->close();

} catch (Exception $e) {
    $__conn->close();
}

if ($_IS_OWNER) { $_IS_ADMIN = true; }
if ($_IS_ADMIN) { $_IS_MEMBER = true; }

?>