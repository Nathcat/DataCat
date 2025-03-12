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
        <div class="content">
            <?php include("header.php"); ?>

            <div class="main align-center">
                <?php 
                include("start-session.php");

                if (array_key_exists("login-error", $_SESSION)) {
                    echo "<div class='error-card'><h2>Login failed</h2><p>" . $_SESSION["login-error"] . "</p></div>";
                }

                if (array_key_exists("user", $_SESSION)) { 
                    if (array_key_exists("return-page", $_GET)) {
                        header("Location: " . $_GET["return-page"]);
                    }

                    include("user-view.php"); 
                } 
                else { 
                    if (!array_key_exists("newUser", $_GET)) { 
                        include("login-form.php"); 
                    } 
                    else {
                        include("new-user-form.php");
                    }
                }
                ?>
            </div>

            <?php include("footer.php"); ?>
        </div>
    </body>
</html>