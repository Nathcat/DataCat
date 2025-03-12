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

            <h2>Create a new app</h2>
            <p>
                This page allows you to create a new app
                which you can use to store and retrieve data
                through DataCat.
            </p>

            <div class="content-card">
                <input id="app-name-entry" type="text" placeholder="App name..." />
                <button onclick="new_app($('#app-name-entry').val(), () => { goto('/app/apps') }, alert)">Create new app</button>
            </div>

            <a href="..">Go back</a>
        </div>

        <?php include("../../../footer.php"); ?>
    </div>
</body>

</html>