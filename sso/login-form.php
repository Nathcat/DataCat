<script>
    function login_form_callback(response) {
        let fd = new FormData();

        console.log(response);
        
        if (response.status === "success") {
            fd.set("user", JSON.stringify(response.user));
        }
        else {
            fd.set("login-error", response.message);
        }
        
        fetch("login.php", {
            method: "POST",
            body: fd
        })
        .then((r) => { if (fd.has("return-page")) window.location = fd.get("return-page"); else location.reload(); });
    }
</script>

<div class="sliding-entry-container">
    <input tabindex="-1" class="big-entry" type="text" id="username-entry" placeholder="Enter your username..."/>
    <input tabindex="-1" style="left: 100%; top: 0;" class="big-entry" type="password" id="password-entry" placeholder="Enter your password..."/>
</div>

<a style="z-index: 1;"href="?newUser">Or, create a new user</a>

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
            }
            else {
                fd.set("login-error", response.message);
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