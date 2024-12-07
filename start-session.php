<?php
session_name("AuthCat-SSO");
//session_set_cookie_params(0, "/", ".nathcat.net");
session_start();

$_DATA_BASE_URL = "https://data.nathcat.net";

if ($_SERVER["SERVER_NAME"] === "localhost") {
    $_SESSION["user"] = [
        "fullName" => "Nathan Baines",
        "pfpPath" => "1.png",
        "username" => "Nathcat",
        "id" => 1,
        "password" => "abcd"
    ];
}
else if (!array_key_exists("user", $_SESSION)) {
    header("Location: https://data.nathcat.net/sso/?return-page=https://data.nathcat.net" . $_SERVER["REQUEST_URI"]);
}
?>