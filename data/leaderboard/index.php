<!DOCTYPE html>
<html>

<head>
    <title>Leaderboard</title>

    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="stylesheet" href="https://solitaire.nathcat.net/static/css/home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <div class="content-card">
        <h1>Leaderboard view</h1>

        <div class="row">
            <button id="asc-button">Order by ascending</button>
            <button id="desc-button">Order by descending</button>
        </div>

        <div class="row">
            <p>Number of results: </p>
            <input id="limit" type="number" placeholder="Number of records" value="<?php if (array_key_exists("limit", $_GET)) echo $_GET["limit"]; ?>"></input>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);

        $("#asc-button").on("click", function() {
            urlParams.set("orderBy", "ASC");
            location = "/data/leaderboard/?" + urlParams.toString();
        });

        $("#desc-button").on("click", function() {
            urlParams.set("orderBy", "DESC");
            location = "/data/leaderboard/?" + urlParams.toString();
        });

        $("#limit").on("change", function() {
            urlParams.set("limit", $(this).val());
            location = "/data/leaderboard/?" + urlParams.toString();
        });
    </script>

    <?php
    if (!array_key_exists("id", $_GET)) {
        die("<h1>Invalid request!</h1><p>You must specify the leaderboard ID!</p>");
    }

    if (!array_key_exists("orderBy", $_GET)) {
        //die("<h1>Invalid request!</h1><p>You must specify the orderBy!</p>");
        $_GET["orderBy"] = "DESC";
    } else {
        if (strtolower($_GET["orderBy"]) !== "asc" && strtolower($_GET["orderBy"]) !== "desc") {
            die("<h1>Invalid request!</h1><p>orderBy must be either ASC or DESC</p>");
        }
    }

    if (array_key_exists("limit", $_GET)) {
        define("LIMIT", $_GET["limit"]);
    }

    $conn = new mysqli("localhost:3306", "Data", "", "DataCat");

    if ($conn->connect_error) {
        die("<h1>Invalid request!</h1><p>Failed to connect to the database: " . $conn->connect_error . "</p>");
    }

    try {
        mysqli_report(MYSQLI_REPORT_ALL);
        $stmt = $conn->prepare("SELECT `name` FROM Leaderboards WHERE id = ?");
        $stmt->bind_param("i", $_GET["id"]);
        $stmt->execute();
        $leaderboard_name = $stmt->get_result()->fetch_assoc()["name"];

        echo "<div class='leaderboard'><h1>$leaderboard_name</h1>";

        $stmt = $conn->prepare("SELECT SSO.Users.username AS 'username', SSO.Users.fullName AS 'fullName', SSO.Users.pfpPath AS 'pfpPath', `value` FROM Leaderboards_Data JOIN SSO.Users ON `user` = SSO.Users.id WHERE leaderboard = ? ORDER BY `value` " . $_GET["orderBy"] . (defined("LIMIT") ? " LIMIT ?" : ""));
        if (defined("LIMIT")) $stmt->bind_param("ii", $_GET["id"], $_GET["limit"]);
        else $stmt->bind_param("i", $_GET["id"]);
        $stmt->execute();

        $set = $stmt->get_result();
        while ($row = $set->fetch_assoc()) {
            echo "<div style='margin: 5px 20px 5px 20px; width: 95%; box-sizing: border-box;' class='row justify-center align-center'><div class='small-profile-picture'><img src='https://cdn.nathcat.net/pfps/" . $row["pfpPath"] . "'></div><h2 style='margin-left: 20px;'>" . $row["fullName"] . "</h2><span class='spacer' style='margin: 0 5px 0 5px; border: 1px solid var(--tertiary-color);'></span><h2>" . $row["value"] . "</h2></div>";
        }

        echo "</div>";
    } catch (Exception $e) {
        $conn->close();
        die("<h1>Invalid request!</h1><p>$e</p>");
    }

    $conn->close();

    ?>

</body>

</html>