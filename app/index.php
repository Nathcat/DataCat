<!DOCTYPE html>
<html>

<head>
    <title>DataCat</title>

    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="../static/styles/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../static/scripts/data.js"></script>
</head>

<body>
    <div class="content">
        <?php include("../header.php"); ?>

        <div class="main align-center">
            <?php include("subheader.php"); ?>

            <h2>Welcome, <?php echo $_SESSION["user"]["fullName"]; ?></h2>
        </div>

        <?php include("../footer.php"); ?>
    </div>
</body>

</html>