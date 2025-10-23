<!DOCTYPE html>
<html>
    <head>
        <title>AuthCat</title>

        <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
        <link rel="stylesheet" href="styles/sso.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
        <link rel="icon" href="/sso/AuthCat.png">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="js/sso.js"></script>
        <script src="js/user-search.js"></script>
    </head>
    
    <body>
        <?php if (!array_key_exists("t", $_GET)) : ?>
            <h1>Invalid request!</h1>
            <div class="content-card">
                <p>
                    You must specify your reset token in order to access this page!
                </p>
            </div>
        <?php else :
            $DB_server = "localhost:3306";
            $DB_user = "sso";
            $DB_pass = "";
            $DB_schema = "SSO";
            $conn = new mysqli($DB_server, $DB_user, $DB_pass, $DB_schema);
            
            if ($conn->connect_error) : ?>
                <h1>Connect error!</h1>
                <div class="content-card">
                    <p>
                        Failed to connect to database!
                    </p>
                </div>
            <?php else :
                try {
                    $stmt = $conn->prepare("SELECT Users.* FROM PasswordResetToken JOIN Users ON PasswordResetToken.id = Users.id WHERE token = sha2(?, '256');");
                    $stmt->bind_param("s", $_GET["t"]);
                    
                    if ($stmt->execute()) :
                        $set = $stmt->get_result();
                        $user = $set->fetch_assoc();
                        $stmt->close();
                        ?>
                            <h1>Reset password for <i><?php echo $user["username"]; ?></i></h1>
                            <div class="content-card">
                                <input id="pass1" type="text" placeholder="New password..." />
                                <input id="pass2" type="text" placeholder="New password again..." />
                            </div>
                        <?php
                    else :
                        $stmt->close();
                        $conn->close();
                        ?>
                        <h1>Failed to validate token</h1>
                        <div class="content-card">
                            <p>
                                Failed to valid token with the database!
                            </p>
                        </div>
                        <?php
                    endif;
                }
            endif;
        ?>
            
        <?php endif ; ?>
    
    </body>
</html>
