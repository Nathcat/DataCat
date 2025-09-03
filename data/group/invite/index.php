<?php
include("../../../start-session.php");

if (!array_key_exists("token", $_GET)) {
    $ERR_MSG = "You must provide an invite token!";
    include("__error.php");
    exit();
}

$TOKEN = $_GET["token"];

include("../start-session.php");

if (!array_key_exists("user", $_SESSION)) {
    $ERR_MSG = "You are not logged in!";
    include("__error.php");
    exit();
}

$conn = new mysqli("localhost:3306", "Data", "", "DataCat");

if ($conn->connect_error) {
    $ERR_MSG = "Failed to connect to database!";
    include("__error.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT `Groups`.`name` as 'name', SSO.Users.username as 'username', SSO.Users.fullName as 'fullName', SSO.Users.pfpPath as 'pfpPath' FROM `Group_Invites` JOIN `Groups` ON `Group_Invites`.`group` = `Groups`.`id` JOIN SSO.Users ON `Group_Invites`.`inviter` = SSO.Users.id WHERE `token` = ? AND `invitee` = ?;");
    $stmt->bind_param("si", $TOKEN, $_SESSION["user"]["id"]);
    $stmt->execute();

    $r = array();
    $set = $stmt->get_result();
    while ($row = $set->fetch_assoc()) {
        array_push($r, $row);
    }

    if (count($r) == 0) {
        $ERR_MSG = "The specified invite could not be found, or you do not have access to it!";
        include("__error.php");
        $conn->close();
        exit();
    }
    else if (count($r) != 1) {
        $ERR_MSG = "An invalid number of invites was returned from the query! (" . count($r) . ")";
        include("__error.php");
        $conn->close();
        exit();
    }

} catch (Exception $e) {
    $conn->close();
    $ERR_MSG = "Error while searching for invite! $e";
    include("__error.php");
    exit();
}

$conn->close();

$invite = $r[0];
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
</head>

<body>
    <div class="content">
        <?php include("../../../header.php");?>

        <div class="main align-center">
            <h2>You have been invited to a group!</h2>
            
            <h1><?php echo $invite["name"]; ?></h1>

            <div class="content-card">
                <h2>Who sent the invite?</h2>

                <div class="profile-picture">
                    <img src="<?php echo $invite["pfpPath"];?>" />
                </div>

                <h3><?php echo $invite["fullName"]; ?></h3>
                <h4><i><?php echo $invite["username"]; ?></i></h4>

            </div>

            <p><?php echo $ERR_MSG; ?></p>
        </div>

        <?php include("../../../footer.php"); ?>
    </div>
</body>

</html>