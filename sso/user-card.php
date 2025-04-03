<?php
header("Content-Type: text/html");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!array_key_exists("id", $_GET)) {
    echo "Please provide user id!";
}

$conn = new mysqli("localhost:3306", "sso", "", "SSO");
$stmt = $conn->prepare("SELECT username, fullName, pfpPath FROM Users WHERE id = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute(); $res = $stmt->get_result()->fetch_assoc();

if ($res) : ?>
    <div style="border: 2px solid #aaaaaa; justify-content: start;" class="content-card row align-center">
        <div class="small-profile-picture">
            <img src="/pfps/<?php echo $res["pfpPath"]; ?>">
        </div>
        <span class="half-spacer"></span>
        <div class="column align-center justify-center">
            <h3><?php echo $res["fullName"]; ?></h3>
            <p><?php echo $res["username"]; ?></p>
        </div>
    </div>
<?php else : ?>
    This user does not exist!
<?php endif;

$stmt->close();
$conn->close();

?>
