<!DOCTYPE html>
<html>

<head>
    <title>DataCat - New app</title>

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
        <?php include("../../../header.php"); include("../../../is-permitted.php"); if ($__IS_PERMITTED__ === 0) header("Location: /");?>

        <div class="main align-center">
            <?php include("../../subheader.php"); ?>

            <h2>Create a new leaderboard</h2>
            <p>
                This page allows you to create a new leaderboard for
                one of your apps.
            </p>

            <div class="content-card">
                <select id='lb-app'></select>
                <input id='lb-name' type="text" placeholder="Leaderboard name..."/>
                <button onclick='if ($("#lb-name").val() === "") { alert("You must enter a name!"); } else { new_leaderboard($("#lb-name").val(), parseInt($("#lb-app").val()), () => { goto("/app/leaderboards"); }, alert); }'>Create leaderboard</button>
            </div>

            <a href="..">Go back</a>

            <script>
                get_apps((apps) => {
                    if (apps.length == 0) {
                        alert("You must create an app to assign a leaderboard to first!");
                        location = "/app/apps";
                    }
                    else {
                        apps.forEach((element) => {
                            document.getElementById("lb-app").innerHTML += "<option value='" + element.id + "'>" + element.name + "</option>";
                        });
                    }
                }, alert);
            </script>
        </div>

        <?php include("../../../footer.php"); ?>
    </div>
</body>

</html>