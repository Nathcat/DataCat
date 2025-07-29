<!DOCTYPE html>
<html>

<head>
    <title>DataCat - New webhook</title>

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
        <?php include("../../../header.php");
        include("../../../is-permitted.php");
        if ($__IS_PERMITTED__ === 0) header("Location: /"); ?>

        <div class="main align-center">
            <?php include("../../subheader.php"); ?>

            <h2>Create a new webhook</h2>
            <p>
                This page allows you to create a new webhook for a leaderboard.
            </p>

            <div class="content-card">
                <input id='api-key' type="text" placeholder="API key..."></input>
                <select id='wb-lb'></select>
                <input id="wh-name" type="text" placeholder="Webhook name..."></input>
                <input id="wh-url" type="url" placeholder="Webhook URL..."></input>
                <button id='submit-button' onclick='if ($("#wh-name").val() === "" || $("#wh-url").val() === "") { alert("You must enter a name and URL!"); } else { add_webhook($("#wh-name").val(), $("#wh-url").val(), $("#wh-lb").val(), $("#api-key").val(), () => { goto("/app/webhooks"); }, alert); }'>Create Webhook</button>
            </div>

            <a href="..">Go back</a>

            <script>
                get_leaderboards((lbs) => {
                    if (lbs.length == 0) {
                        alert("You must create a leaderboard to create a webhook!");
                        window.location = "/app/webhooks";
                    } else {
                        lbs.forEach((element) => {
                            document.getElementById("wh-lb").innerHTML += "<option value='" + element.id + "'>" + element.name + "</option>";
                        });
                    }
                }, alert);

                $("#wh-url").keyup(function(event) {
                    if (event.keyCode === 13) {
                        $("#submit-button").click();
                    }
                });
            </script>
        </div>

        <?php include("../../../footer.php"); ?>
    </div>
</body>

</html>