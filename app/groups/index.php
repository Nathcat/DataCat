<!DOCTYPE html>
<html>

<head>
    <title>DataCat - Groups</title>

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

            <h2>Groups you own</h2>
            <div id="owned-group-list" class="column align-center" style="width: calc(100% - 50px); margin: 25px;"></div>

            <h2>Groups you are a member of</h2>
            <div id="member-group-list" class="column align-center" style="width: calc(100% - 50px); margin: 25px;"></div>

            <a href="new">Create a new group</a>
            
            <script>
                get_groups((r) => {
                    r.owned.forEach(element => {
                        document.getElementById("owned-group-list").innerHTML += "<div class='content-card app-record group-record' style='width: 100%; margin: 10px;'><h3>" + element.name + "</h3><span class='quarter-spacer'></span><h4>Created by <i>" + element.ownerUsername + "</i></h4></div>";
                    });

                    if (r.owned.length === 0) {
                        $("#owned-group-list").html("<h3><i>You own no groups!</i></h3>")
                    }

                    r.memberOf.forEach(element => {
                        document.getElementById("member-group-list").innerHTML += "<div class='content-card app-record group-record' style='width: 100%; margin: 10px;'><h3>" + element.name + "</h3><span class='quarter-spacer'></span><h4>Created by <i>" + element.ownerUsername + "</i></h4></div>";
                    });

                    if (r.memberOf.length === 0) {
                        $("#member-group-list").html("<h3><i>You are a member of no groups!</i></h3>")
                    }
                }, alert);
            </script>
        </div>

        <?php include("../../footer.php"); ?>
    </div>
</body>

</html>