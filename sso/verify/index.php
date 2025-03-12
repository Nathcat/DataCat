<?php 
session_name("AuthCat-SSO");
session_set_cookie_params(0, '/', ".nathcat.net"); 
session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>AuthCat - Verify <?php echo$_SESSION["user"]["username"]; ?></title>

        <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>

    <body>
        <div class="content">
            <?php include("../header.php"); ?>

            <div class="main align-center">

                <?php 
                if(!isset($_POST["code-submitted"])) : ?>
                    <?php if($_SESSION["user"]["verified"]) :?>
                        <div class="content-card">
                            <h2>You are already verified!</h2>
                            <a href="/sso">Return home</a>
                        </div>
                    <?php else : ?>
                        <div class="content-card">
                            <h2>Account verification</h2>
                            <p>Within 15 minutes of making your account you should have received a welcome email, and an email containing a verification code. Please enter this code here:</p>
                            <form method="POST" action="">
                                <input type="text" placeholder="Verification code..." name="verify-code"/>
                                <input type="submit" name="code-submitted" value="Submit code" />
                            </form>
                        </div>
                    <?php endif; ?>
                <?php else : 
                    $code = $_POST["verify-code"];
                    $conn = new mysqli("localhost:3306", "sso", "", "SSO");
                
                    if ($conn->connect_error) {
                        echo "{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}";
                        die();
                    }
                
                    $stmt = $conn->prepare("SELECT * FROM VerifyCodes WHERE id = ?");
                    $stmt->bind_param("i", $_SESSION["user"]["id"]);
                    $stmt->execute();
                    $res_set = $stmt->get_result();

                    $count = 0;
                    $success = false;
                    while ($res = $res_set->fetch_assoc()) {
                        if ($count != 0) {
                            die("<div class='error-card'><h2>DB Error!</h2><p>The database returned multiple verification codes for this user!</p></div>");
                        }
                    
                        $count++;
                        $success = $res["code"] == $code;
                    }
                
                    $stmt->close();
                
                    if ($success) :
                        $stmt = $conn->prepare("UPDATE Users SET verified = 1 WHERE id = ?");
                        $stmt->bind_param("i", $_SESSION["user"]["id"]);
                        $stmt->execute();
                        $stmt->close();
                        $stmt = $conn->prepare("DELETE FROM VerifyCodes WHERE id = ?");
                        $stmt->bind_param("i", $_SESSION["user"]["id"]);
                        $stmt->execute();
                        $stmt->close();
                        $_SESSION["user"]["verified"] = 1;
                    ?>
                        <div class="content-card">
                            <h2>Verification completed!</h2>
                            <a href="/sso">Return to home</a>
                        </div>
                    <?php else : ?>
                        <div class="error-card">
                            <h2>Verification failed</h2>
                            <p>An incorrect code was entered!</p>
                            <a href="verify.php">Try again</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            
            </div>

            <?php include("../footer.php"); ?>
        </div>
    </body>
</html>