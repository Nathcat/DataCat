<script>
    function login_form_callback(response) {
        let fd = new FormData();

        console.log(response);

        if (response.status === "success") {
            fd.set("user", JSON.stringify(response.user));
        } else {
            fd.set("login-error", response.message);
        }

        fetch("login.php", {
                method: "POST",
                body: fd
            })
            .then((r) => {
                if (fd.has("return-page")) window.location = fd.get("return-page");
                else location.reload();
            });
    }
</script>

<div id="login-form-container">
    <div id="intro">
        <img src="AuthCat.png" style="width: 25%; aspect-ratio: 1;">

        <h1>Welcome to AuthCat!</h1>
        <p>
            AuthCat is the heart of the Nathcat network, providing a Single
            Sign On system for all its applications and services.
        </p>
        <p>
            Please enter your details to login, or hit the button below to create a new user.
        </p>

        <a href="https://blog.nathcat.net/?page=16" style="margin: 25px;">How does this page work?</a>

        <button onclick="location = '?newUser';">Create a new user</button>

        <button onclick="sso_forgot_password(() => { alert('A link to reset your password has been sent to the provided email.'); }, alert)">Forgot password</button>
    </div>

    <div class="sliding-entry-container">
        <input tabindex="-1" class="big-entry" type="text" id="username-entry" placeholder="Enter your username..." />
        <input tabindex="-1" style="left: 100%; top: 0;" class="big-entry" type="password" id="password-entry" placeholder="Enter your password..." />
    </div>
</div>

<script src="js/slidingEntry.js"></script>
<script>
    if (window.localStorage.getItem("AuthCat-QuickAuthToken") !== null) {
        sso_try_login("", "",
            (response) => {
                let fd = new FormData();

                if (response.status === "success") {
                    fd.set("user", JSON.stringify(response.user));
                    <?php
                    if (array_key_exists("return-page", $_GET)): ?>
                        window.location = "<?php echo $_GET["return-page"]; ?>";
                    <?php endif; ?>

                    location.reload();
                    return;
                }

                window.localStorage.removeItem("AuthCat-QuickAuthToken");
            }
        );
    }

    slidingEntry_setup(["username-entry", "password-entry"]);

    slidingEntry_finished_entry_callback = () => {
        sso_try_login(
            document.getElementById("username-entry").value,
            document.getElementById("password-entry").value,

            (response) => {
                let fd = new FormData();

                if (response.status === "success") {
                    fd.set("user", JSON.stringify(response.user));
                } else {
                    alert(response.message);
                    return;
                }

                <?php
                if (array_key_exists("return-page", $_GET)): ?>
                    window.location = "<?php echo $_GET["return-page"]; ?>";
                <?php endif; ?>

                location.reload();
            }
        )
    };
</script>