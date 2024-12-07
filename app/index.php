<!DOCTYPE html>
<html>

<head>
    <title>DataCat</title>

    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="../static/styles/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../static/scripts/data.js"></script>
</head>

<body>
    <div class="content">
        <?php include("../header.php"); ?>
        <?php
        $conn = new mysqli("localhost:3306", "Data", "", "DataCat");
        $stmt = $conn->prepare("INSERT IGNORE INTO UserData (id) VALUES (?)");
        $stmt->bind_param("i", $_SESSION["user"]["id"]);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("SELECT is_permitted FROM UserData WHERE id = ?");
        $stmt->bind_param("i", $_SESSION["user"]["id"]);
        $stmt->execute();
        $allowed = $stmt->get_result()->fetch_assoc()["is_permitted"];
        $stmt->close();

        if ($allowed === 0) : ?>

            <div class="main align-center justify-center">
                <h1>Welcome to DataCat</h1>
                <h2><i>You don't have access!</i></h2>

                <div class="content-card">
                    <p>
                        Because of the nature of this service, not everyone is allowed to access it.
                    </p>
                    <p>
                        At the moment, access is requested and granted on a case-by-case basis, if you
                        require access, please contact Nathan Baines to make your request.
                    </p>
                </div>
            </div>

        <?php else : ?>

            <div class="main">
                <?php
                $mysql_username = $_SESSION["user"]["id"] . "_" . $_SESSION["user"]["username"];
                $mysql_user_db = $mysql_username . "_db";

                $stmt = $conn->prepare("SELECT EXISTS(SELECT 1 FROM mysql.user WHERE user=?) AS 'exists';");
                $stmt->bind_param("s", $mysql_username);
                $stmt->execute();
                $user_exists = $stmt->get_result()->fetch_assoc()["exists"];
                $stmt->close();

                if ($user_exists === 0) {
                    $stmt = $conn->prepare("CREATE USER '$mysql_username'@'%' IDENTIFIED BY '" . $_SESSION["user"]["password"] . "'");
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare("CREATE SCHEMA `$mysql_user_db`");
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare("GRANT ALL ON `$mysql_user_db`.* TO '$mysql_username'@'%'");
                    $stmt->execute();
                    $stmt->close();
                }

                $conn->close();
                ?>

                <div id="schemas-list" class="column" style="align-items: start; justify-content: start"></div>

                <script>
                    data_update_db_info();
                </script>

                <div id="query" class="column align-center">
                    <textarea class="query-entry"></textarea>

                    <div id="results-container"></div>
                </div>

                <script>
                    $(".query-entry").on("keypress", function(e) {
                        if (e.keyCode === 13 && e.ctrlKey) {
                            fetch("../run-query.php", {
                                method: "POST",
                                credentials: "include",
                                body: $(this).val()
                            }).then((r) => r.json()).then((r) => {
                                data_update_db_info();

                                if (r.status === "success") {
                                    for (let i = 0; i < r.results.length; i++) {
                                        document.getElementById("results-container").innerHTML += data_create_results_table(r.results[i]);
                                    }
                                }
                                else {
                                    alert(r.message);
                                }
                            });
                        }
                    });

                    $(".query-entry").on("input", function(e) {
                        this.style.height = "auto";
                        this.style.height = this.scrollHeight + 'px';
                    });
                </script>
            </div>

        <?php endif; ?>

        <?php include("../footer.php"); ?>
    </div>
</body>

</html>