<?php
include("../../../start-session.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>GroupCat - Invite</title>

    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <style>
        #accept {
            border: 5px solid #009c00ff;
        }

        #accept:hover {
            transition: 500ms;
            background-color: #00ff00;
        }

        #decline {
            border: 5px solid #9c0000ff;
        }

        #decline:hover {
            transition: 500ms;
            background-color: #ff0000;
        }
    </style>

    <script>
        let token = "<?php echo $TOKEN; ?>";

        function accept() {
            fetch("https://data.nathcat.net/data/accept-group-invite.php", {
                method: "POST",
                credentials: "include",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({"token": token})
            }).then((r) => r.json()).then((r) => {
                if (r.status == "success") {
                    alert("You have joined <?php echo $invite["name"]; ?>!");
                    window.location = "https://apps.nathcat.net";
                }
                else {
                    alert(r.message);
                }
            });
        }

        function decline() {
            fetch("https://data.nathcat.net/data/decline-group-invite.php", {
                method: "POST",
                credentials: "include",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({"token": token})
            }).then((r) => r.json()).then((r) => {
                if (r.status == "success") {
                    alert("Invitation was declined!");
                    window.location = "https://apps.nathcat.net";
                }
                else {
                    alert(r.message);
                }
            });
        }
    </script>
</head>

<body>
    <div class="content">
        <?php include("../../header.php");?>

        <div class="main align-center">
            <?php 
            if (!array_key_exists("id", $_GET)) : ?>
                <h1>No group specified!</h1>
            <?php else : 
                
                $conn = new mysqli("localhost:3306", "Data", "", "DataCat");

                if ($conn->connect_error) {
                    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
                }

                try {
                    $stmt = $conn->prepare("SELECT *, SSO.Users.username, SSO.Users.fullName, SSO.Users.pfpPath FROM `Groups` JOIN SSO.Users ON `owner` = SSO.Users.id WHERE `Groups`.`id` = ?");
                    $stmt->bind_param("i", $_GET["id"]);
                    $stmt->execute();
                
                    $r = array();
                    $set = $stmt->get_result();
                    while ($row = $set->fetch_assoc()) {
                        array_push($r, $row);
                    }
                
                    if (count($r) == 0) : ?>
                        <h1>Group not found!</h1>
                    <?php else :

                        $group = $r[0];

                        $stmt->close();
                        $stmt = $conn->prepare("SELECT SSO.Users.username, SSO.Users.fullName, SSO.Users.pfpPath FROM `Group_Members` JOIN SSO.Users ON `Group_Members`.`user` = SSO.Users.id WHERE `group` = ?");
                        $stmt->bind_param("i", $_GET["id"]);
                        $stmt->execute();
                        $set = $stmt->get_result();

                        $members = array();
                        while ($row = $set->fetch_assoc()) {
                            array_push($members, $row);
                        }
                        
                        ?>

                        <div class="content-card column align-center justify-center">
                            <h1><?php echo $group["name"]; ?></h1>

                            <h3><i>Created by</i></h3>

                            <div class="profile-picture">
                                <img src="https://cdn.nathcat.net/pfps/<?php echo $group["pfpPath"]; ?>" />
                            </div>

                            <h3><?php echo $group["fullName"]; ?></h3>
                            <h4><i><?php echo $group["username"]; ?></i></h4>
                        </div>

                        <div class="column">
                            <?php

                            if (count($members) == 0) {
                                echo "<h2>This group has no members!</h2>";
                            }

                            foreach ($members as $m) {
                                echo "<div style=\"border: 2px solid #aaaaaa; justify-content: start;\" class=\"content-card row align-center\"><div class=\"small-profile-picture\">";
                                echo "<img src=\"/pfps/" . $m["pfpPath"] . "\">";
                                echo "</div><span class=\"half-spacer\"></span><div class=\"column align-center justify-center\">";
                                echo "<h3>" . $m["fullName"] . "</h3>";
                                echo "<p>" . $m["username"] . "</p></div></div>";                                
                            }

                            ?>
                        </div>
                
                    <?php endif;
                } catch (Exception $e) {
                    $conn->close();
                    echo $e;
                }


                $conn->close();
            
                ?>

            <?php endif; ?>

        </div>

        <?php include("../../footer.php"); ?>
    </div>
</body>

</html>