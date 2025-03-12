<?php 
if (array_key_exists("newPfpPath", $_GET)) {
    $_SESSION["user"]["pfpPath"] = $_GET["newPfpPath"];

    $conn = new mysqli("localhost:3306", "sso", "", "SSO");
    $stmt = $conn->prepare("UPDATE Users SET pfpPath = ? WHERE id = ?");
    $stmt->bind_param("si", $_GET["newPfpPath"], $_SESSION["user"]["id"]);
    $stmt->execute(); $stmt->close(); $conn->close();
}

if ($_SESSION["user"]["passwordUpdated"]) : ?>

<div class="user-view-container">
    <div style="grid-area: user-data; width: 100%;" class="column justify-center align-center">
        <h1>Welcome, <?php echo $_SESSION["user"]["fullName"]; ?>.</h1>

        <div class="profile-picture">
            <img src="<?php echo "/pfps/" . $_SESSION["user"]["pfpPath"]; ?>">
        </div>

        <div class="row align-center">
            <input type="file" id="uploadFilePFP" />
            <button onclick="sso_upload_pfp(document.getElementById('uploadFilePFP').files[0])">Upload new profile picture</button>
        </div>

        <div class="content-card" style="width: 100%;">
            <h2>User information</h2>
            <p>Username: <?php echo $_SESSION["user"]["username"] ?></h1></p>
            <p>Email: <?php echo $_SESSION["user"]["email"] ?></h1></p>
            <p>Verified: <?php echo $_SESSION["user"]["verified"] == 1 ? "Yes" : "No, <a href='verify'>Click here to verify</a>" ?></p>
            <a href="docs/policies/privacy-policy.php">View our privacy policy</a>
            <div class="row">
                <button onclick="sso_create_quick_auth()">Save my login info on this browser</button>
                <button onclick="sso_revoke_quick_auth(<?php echo $_SESSION['user']['id']; ?>)">Revoke all sessions</button>
            </div>
        </div>

        <button style="width: 100%;" onclick="var xhr = new XMLHttpRequest(); xhr.onload = function() { location.reload(); }; xhr.open('GET', 'logout.php', true); xhr.send();">Logout</button>
    </div>

    <span></span>
    
    <div style="grid-area: user-search; width: 100%;">
        <div class="content-card column justify-center">
            <h2>User search</h2>
            <input type="text" id="search-username" placeholder="Username..." />
            <input type="text" id="search-fullname" placeholder="Full name..." />
            <button onclick="user_search('search-username', 'search-fullname', 'search-results')">Search</button>
            <div id="search-results" class="column justify-center"></div>

            <script>
                document.getElementById("search-username").addEventListener("keypress", (e) => { 
                    if (e.key == "Enter") {
                        user_search('search-username', 'search-fullname', 'search-results');
                    } 
                });

                document.getElementById("search-fullname").addEventListener("keypress", (e) => { 
                    if (e.key == "Enter") {
                        user_search('search-username', 'search-fullname', 'search-results');
                    } 
                });
            </script>
        </div>
    </div>
</div>

<?php else : ?>
<div class="error-card">
    <h2>Password update!</h2>
    <p>
        In response to feedback on the security of the password system in place on AuthCat, the password system has been updated and improved,
        however this means that all users are required to update their passwords.
    </p>
    <p>
        You can put the same password in again if you wish, although it is of course suggested that you choose a new, more secure password.
    </p>
</div>

<div class="column align-center justify-center">
    <input type="password" id="new-password" placeholder="Enter your new password..." />
    <input type="password" id="new-password2" placeholder="Repeat your new password..." />
    <button onclick="sso_update_password(document.getElementById('new-password').value, document.getElementById('new-password2').value)">Submit new password</button>
</div>

<script>
    document.getElementById("new-password").addEventListener("keypress", (e) => { 
        if (e.key == "Enter") {
            sso_update_password(document.getElementById('new-password').value, document.getElementById('new-password2').value)
        } 
    });

    document.getElementById("new-password2").addEventListener("keypress", (e) => { 
        if (e.key == "Enter") {
            sso_update_password(document.getElementById('new-password').value, document.getElementById('new-password2').value)
        } 
    });
</script>

<?php endif; ?>