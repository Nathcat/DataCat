<!DOCTYPE html>
<html>

<head>
    <title>DataCat - Apps</title>

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
        <?php include("../../header.php"); ?>

        <div class="main align-center">
            <?php include("../subheader.php"); ?>

            <h2>Your Apps</h2>

            <div id="app-list" class="column align-center" style="width: calc(100% - 50px); margin: 25px;"></div>

            <a href="new">Create a new app</a>
            
            <script>
                let ask_delete = (id, name) => {
                    if (confirm("Are you sure you want to delete " + name + "?")) {
                        delete_app(id, () => location.reload(), alert);
                    }
                };

                let copy_api_key = (key) => {
                    navigator.clipboard.writeText(key);
                    $("#api-key-button").text("Copied!");

                    setTimeout(() => { $("#api-key-button").text("Copy API key"); }, 2000);
                };

                get_apps((apps) => {
                    apps.forEach(element => {
                        document.getElementById("app-list").innerHTML += "<div class='content-card app-record' style='width: 100%; margin: 10px;'><h3>" + element.name + "</h3><span class='half-spacer'></span><button id='api-key-button' onclick=\"copy_api_key('" + element.apiKey + "')\" class='delete-button'>Copy API key</button><span class='half-spacer'></span><button class='delete-button' onclick=\"ask_delete(" + element.id + ", '" + element.name + "')\"><span class='material-symbols-outlined'>delete</span></button></div>";
                    });

                    if (apps.length === 0) {
                        $("#app-list").html("<h3><i>You have no apps!</i></h3>")
                    }
                }, alert);
            </script>
        </div>

        <?php include("../../footer.php"); ?>
    </div>
</body>

</html>