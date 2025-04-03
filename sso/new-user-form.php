<script>
    function create_new_user_callback(response) {

        if (response.status == "success") {
            window.location = "<?php echo dirname($_SERVER["PHP_SELF"]); ?>";
        } 
        else {
            alert(response.message);
        }
    }
</script>

<div class="sliding-entry-container">
    <input class="big-entry" id="login-username" type="text" name="username" placeholder="Enter username..." />
    <input class="big-entry" style="left: 100%" id="login-email" type="email" name="email" placeholder="Enter your email..." />
    <input class="big-entry" style="left: 200%" id="login-password" type="password" name="password" placeholder="Enter password..." />
    <input class="big-entry" style="left: 300%" id="login-password2" type="password" name="password2" placeholder="Re-enter password..." />
    <input class="big-entry" style="left: 400%" id="login-fullName" type="text" name="fullName" placeholder="Enter your name..." />
</div>

<a style="z-index: 1;" href="<?php echo dirname($_SERVER["PHP_SELF"]); ?>">Or, login</a>
<a style="z-index: 1;" href="docs/policies/privacy-policy.php">View our privacy policy</a>

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