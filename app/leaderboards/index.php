<!DOCTYPE html>
<html>

<head>
    <title>DataCat - Leaderboards</title>

    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0&icon_names=delete" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/static/styles/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/static/scripts/data.js"></script>
</head>

<body>
    <div class="content">
        <?php include("../../header.php"); include("../../is-permitted.php"); if ($__IS_PERMITTED__ === 0) header("Location: /");?>

        <div class="main align-center">
            <?php include("../subheader.php"); ?>

            <h2>Leaderboards</h2>

            <div id="leaderboard-list" class="column align-center" style="width: calc(100% - 50px); margin: 25px;"></div>

            <a href="new">Create a new leaderboard</a>
            
            <script>
                let ask_delete = (id, name) => {
                    if (confirm("Are you sure you want to delete " + name + "?")) {
                        delete_leaderboard(id, () => location.reload(), alert);
                    }
                };

                let copy_id = (key) => {
                    navigator.clipboard.writeText(key);
                    $("#id-button").text("Copied!");

                    setTimeout(() => { $("#id-button").text("Copy ID"); }, 2000);
                };

                get_leaderboards((lbs) => {
                    lbs.forEach(element => {
                        document.getElementById("leaderboard-list").innerHTML += "<div class='content-card app-record' style='width: 100%; margin: 10px;'><h3>" + element.name + "</h3><p style='margin-left: 25px;'>Belonging to <i>" + element.appName + "</i></p><span class='half-spacer'></span><button id='id-button' onclick=\"copy_id(" + element.id + ")\" class='delete-button'>Copy ID</button><span class='half-spacer'></span><button class='delete-button' onclick=\"ask_delete(" + element.id + ", '" + element.name + "')\"><span class='material-symbols-outlined'>delete</span></button></div>";
                    });

                    if (lbs.length === 0) {
                        $("#leaderboard-list").html("<h3><i>You have no leaderboards!</i></h3>")
                    }
                }, alert);
            </script>
        </div>

        <?php include("../../footer.php"); ?>
    </div>
</body>

</html>