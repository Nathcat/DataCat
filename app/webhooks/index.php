<!DOCTYPE html>
<html>

<head>
    <title>DataCat - Webhooks</title>

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

            <h2>Webhooks</h2>

            <div class="content-card">
                <p>Webhooks allow you to send updates into a discord server when your leaderboard is updated!</p>
            </div>

            <div id="webhook-list" class="column align-center" style="width: calc(100% - 50px); margin: 25px;"></div>

            <a href="new">Create a new webhook</a>
            
            <script>
                let html = "";

                fetch("https://data.nathcat.net/data/get-webhooks.php", {
                    method: "GET",
                    credentials: "include"
                }).then((r) => r.json()).then((r) => {
                    if (r.status === "success") {
                        let html = "";

                        for (let i = 0; i < r.results.length; i++) {
                            html += "<div id='" + r.results[i].id + "' class='content-card'><h2>" + r.results[i].name + "</h2><p>Sent on update of " + r.results[i].leaderboardName + "</p><code><i>" + r.results[i].url + "<i></code></div>"    
                        }

                        $("#webhook-list").html(html);
                    }
                    else {
                        alert("Failed to get webhooks for the current user!");
                    }
                });
            </script>
        </div>

        <?php include("../../footer.php"); ?>
    </div>
</body>

</html>