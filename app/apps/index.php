<!DOCTYPE html>
<html>

<head>
    <title>DataCat - Apps</title>

    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/static/styles/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/static/scripts/data.js"></script>
</head>

<body>
    <div class="content">
        <?php include("../../header.php"); ?>

        <div class="main align-center">
            <?php include("../subheader.php"); ?>

            <h2>Your Apps</h2>

            <div id="app-list" class="column align-center" style="width: calc(100% - 50px); margin: 25px;"></div>

            <a href="new">Create a new app</a>
            
            <script>
                get_apps((apps) => {
                    apps.forEach(element => {
                        document.getElementById("app-list").innerHTML += "<div class='content-card' style='width: 100%; margin: 10px;'><h3>" + element.name + "</h3></div>";
                    });
                }, alert);
            </script>
        </div>

        <?php include("../../footer.php"); ?>
    </div>
</body>

</html>