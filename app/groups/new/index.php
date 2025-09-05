<!DOCTYPE html>
<html>

<head>
    <title>DataCat - New Group</title>

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

            <h2>Create a new group</h2>
            <p>
                This page allows you to create a new group which you can invite users to.
            </p>

            <div class="content-card">
                <input id='name' type="text" placeholder="Group name..." />
                <button id='submit-button' onclick="if ($('#name').val() !== '') create_group($('#name').val(), () => { goto('/app/groups'); }, alert); else alert('You must provide a name for the new group!'); ">Create group</button>
            </div>

            <a href="..">Go back</a>

            <script>
                $("#name").keyup(function(event) {
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