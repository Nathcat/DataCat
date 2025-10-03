<?php
include("../../start-session.php");

if (array_key_exists("id", $_GET)) :

$MEMBER = 1;
$OWNER = 2;

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    die($conn->connect_error);
}

$ACCESS_LEVEL = 0;

try {
    $stmt = $conn->prepare("SELECT `owner` FROM `Groups` WHERE `id` = ? AND `owner` = ?");
    $stmt->bind_param("ii", $_GET["id"], $_SESSION["user"]["id"]);
    $stmt->execute();

    $set = $stmt->get_result();
    $c = 0;
    while ($row = $set->fetch_assoc()) {
        $c++;
    }

    if ($c != 0) {
        $ACCESS_LEVEL = $OWNER;
    }

    $stmt->close();

    if ($ACCESS_LEVEL == 0) {
        $stmt = $conn->prepare("SELECT `user` FROM `Group_Members` WHERE `group` = ? AND `user` = ?");
        $stmt->bind_param("ii", $_GET["id"], $_SESSION["user"]["id"]);
        $stmt->execute();

        $set = $stmt->get_result();
        $c = 0;
        while ($row = $set->fetch_assoc()) {
            $c++;
        }

        if ($c != 0) {
            $ACCESS_LEVEL = $MEMBER;
        }

        $stmt->close();
    }

} catch (Exception $e) {
    $conn->close();
    die($e);
}

$conn->close();

endif;

?>

<!DOCTYPE html>
<html>

<head>
    <title>DataCat - Group</title>

    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/static/scripts/data.js"></script>

    <style>
        .green {
            border: 5px solid #009c00ff;
        }

        .green:hover {
            transition: 500ms;
            background-color: #00ff00;
        }

        .red {
            border: 5px solid #9c0000ff;
        }

        .red:hover {
            transition: 500ms;
            background-color: #ff0000;
        }
    </style>

    <?php if (array_key_exists("id", $_GET)) : ?>
        <script>
            const g_id = <?php echo $_GET["id"]; ?>;
            const u_id = <?php echo $_SESSION["user"]["id"]; ?>;
            var g_name;

            function confirm_leave_group() {
                if (confirm("Are you sure you want to leave this group? You won't be able to join back unless an admin invites you again!")) {
                    leave_group(g_id, u_id, () => { location = '/app'; }, alert);
                }
            }

            function confirm_delete_group() {
                if (confirm("Are you sure you want to delete this group permanently? This action is irreversible!")) {
                    let x = prompt("Enter the name of this group to confirm", "");
                    if (x === g_name) {
                        delete_group(g_id, () => { location = '/app'; }, alert);
                    }
                }
            }

            function send_invite() {
                let username = prompt("Please enter the username of the user you wish to invite to this group");

                fetch("https://data.nathcat.net/sso/user-search.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({
                        "username": username
                    })
                }).then((r) => r.json()).then((r) => {
                    if (r.status === "success") {
                        let ids = Object.keys(r.results);
                        if (ids.length == 0) {
                            alert("User not found");
                            return;
                        }
                        else if (ids.length != 1) {
                            console.log(r.results);
                            alert("More than one result! Please specify the exact username!");
                            return;
                        }
                        else {
                            invite_to_group(g_id, ids[0], () => { alert("Invite sent!"); }, alert);
                        }
                        
                    }
                    else {
                        alert(r.message);
                    }
                });
            }
        </script>
    <?php endif; ?>
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

                        <div class="content-card column align-center justify-center" style="min-width: 50%; max-width: 100%;">
                            <h1><?php echo $group["name"]; ?></h1>
                            <script> g_name = "<?php echo $group["name"]; ?>"; </script>

                            <h3><i>Created by</i></h3>

                            <div class="profile-picture">
                                <img src="https://cdn.nathcat.net/pfps/<?php echo $group["pfpPath"]; ?>" />
                            </div>

                            <h3><?php echo $group["fullName"]; ?></h3>
                            <h4><i><?php echo $group["username"]; ?></i></h4>

                            <?php if ($ACCESS_LEVEL == $OWNER) : ?>
                                <h2><i><b>You own this group!</b></i></h2>
                            <?php elseif ($ACCESS_LEVEL == $MEMBER) : ?>
                                <h2><i><b>You are a member of this group!</b></i></h2>
                            <?php endif; ?>

                        </div>

                        <div class="content-card column align-center" style="min-width: 50%; max-width: 100%;">
                            <h2>Members</h2>
                            <?php

                            if (count($members) == 0) {
                                echo "<h2>This group has no members!</h2>";
                            }

                            foreach ($members as $m) {
                                echo "<div style=\"width: 95%; border: 2px solid #aaaaaa; justify-content: start;\" class=\"content-card row align-center\"><div class=\"small-profile-picture\">";
                                echo "<img src=\"/pfps/" . $m["pfpPath"] . "\">";
                                echo "</div><span class=\"half-spacer\"></span><div class=\"column align-center justify-center\">";
                                echo "<h3>" . $m["fullName"] . "</h3>";
                                echo "<p>" . $m["username"] . "</p></div></div>";                                
                            }

                            ?>
                        </div>

                        <?php if ($ACCESS_LEVEL == $MEMBER) : ?>
                            <button class="red" onclick="confirm_leave_group()">Leave group</button>
                        <?php elseif ($ACCESS_LEVEL == $OWNER) : ?>
                            <button class="red" onclick="confirm_delete_group()">Delete group</button>
                            <button class="green" onclick="send_invite()">Invite a new user to the group</button>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION["user"]["id"] == 1) : ?>
                            <script>
                                function send_email() {
                                    fetch("./email-group.php", {
                                        method: "POST",
                                        headers: { "Content-Type": "application/json" },
                                        credentials: "include",
                                        body: JSON.stringify({
                                            "group": <?php echo $_GET["id"]; ?>,
                                            "subject": $("#email-subject").val(),
                                            "content": $("#email-content").val()
                                        })
                                    });
                                }
                            </script>

                            <input id="email-subject" type="text" placeholder="Email subject"></input>
                            <textarea id="email-content" placeholder="Email content"></textarea>

                            <button onclick="send_email()">Send email to group</button> 
                        <?php endif; ?>
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