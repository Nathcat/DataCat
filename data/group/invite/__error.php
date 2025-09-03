<!DOCTYPE html>
<html>

<head>
    <title>GroupCat - Invite - Error</title>

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
            <h1>An error occurred!</h1>

            <p><?php echo $ERR_MSG; ?></p>
        </div>

        <?php include("../../../footer.php"); ?>
    </div>
</body>

</html>