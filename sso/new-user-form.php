<script>
    function create_new_user_callback(response) {

        if (response.status == "success") {
            window.location = "<?php echo dirname($_SERVER["PHP_SELF"]); ?>";
        } else {
            alert(response.message);
        }
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
            Enter your details below to create a new user, or hit the button below
            to login with an existing user!
        </p>

        <a href="https://blog.nathcat.net/?page=16" style="margin: 25px;">How does this page work?</a>

        <button onclick="location = '/sso';">Login with existing user</button>
    </div>

    <div class="sliding-entry-container">
        <input class="big-entry" id="login-username" type="text" name="username" placeholder="Enter username..." />
        <input class="big-entry" style="left: 100%" id="login-email" type="email" name="email" placeholder="Enter your email..." />
        <input class="big-entry" style="left: 200%" id="login-password" type="password" name="password" placeholder="Enter password..." />
        <input class="big-entry" style="left: 300%" id="login-password2" type="password" name="password2" placeholder="Re-enter password..." />
        <input class="big-entry" style="left: 400%" id="login-fullName" type="text" name="fullName" placeholder="Enter your name..." />
    </div>
</div>

<script src="js/slidingEntry.js"></script>

<script>
    slidingEntry_setup([
        "login-username",
        "login-email",
        "login-password",
        "login-password2",
        "login-fullName"
    ]);

    slidingEntry_finished_entry_callback = () => {
        sso_create_new_user($("#login-username").val(), $("#login-email").val(), $("#login-password").val(), $("#login-password2").val(), $("#login-fullName").val(), create_new_user_callback);
    };
</script>