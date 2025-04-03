<!DOCTYPE html>
<html>
    <head>
        <title>DataCat</title>

        <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>

    <body>
        <div class="content">
            <?php include("header.php"); include("is-permitted.php")?>
            <?php 
            if ($__IS_PERMITTED__ === 0) : ?>

                <div class="main align-center justify-center">
                    <h1>Welcome to DataCat</h1>
                    <h2><i>You don't have access!</i></h2>

                    <div class="content-card">
                        <p>
                            Because of the nature of this service, not everyone is allowed to access it.
                        </p>
                        <p>
                            At the moment, access is requested and granted on a case-by-case basis, if you 
                            require access, please contact Nathan Baines to make your request.
                        </p>
                    </div>
                </div>

            <?php else : ?>

                <div class="main align-center justify-center">
                    <h1>Welcome to DataCat</h1>
                    <h2><a href="app">Go to application</a></h2>
                </div>

            <?php endif; ?>

            <?php include("footer.php"); ?>
        </div>
    </body>
</html>