const SSO_BASE_URL = "localhost";

function sso_try_login(username, password, callback) {

    let fd = new FormData();
    fd.set("username", username);
    fd.set("password", password);

    if (window.localStorage.getItem("AuthCat-QuickAuthToken") !== null) {
        fd.set("quick-auth-token", window.localStorage.getItem("AuthCat-QuickAuthToken"));
    }

    fetch("/sso/try-login.php", {
        method: "POST",
        body: fd
    })
    .then((response) => response.json())
    .then(callback)
    .catch((error) => {
        console.log("SSO Request failed: " + error);
    });
}

function sso_create_new_user(username, email, password, password2, fullName, callback) {
    let fd = new FormData();
    fd.set("username", username);
    fd.set("email", email);
    fd.set("password", password);
    fd.set("password2", password2);
    fd.set("fullName", fullName);
    fetch("/sso/create-user.php", {
        method: "POST",
        body: fd
    })
    .then((r) => r.json())
    .then(callback);
}

function sso_update_password(new_password, password_reentry) {
    if (new_password === "" || password_reentry === "") {
        alert("One or more fields are empty!");
    }
    else if (new_password !== password_reentry) {
        alert("Passwords do not match!");
    }
    else {

        let fd = new FormData();
        fd.set("password", new_password);

        fetch("/sso/change-password.php", {
            method: "POST",
            body: fd
        }).then((r) => location.reload());

    }
}

function sso_create_quick_auth() {
    if (window.localStorage.getItem("AuthCat-QuickAuthToken") !== null) {
        alert("You have already done this!");
        return;
    }

    fetch("/sso/create-quick-auth.php?by-session", {
        method: "POST",
        credentials: "include"
    }).then((r) => r.json()).then((r) => {
        if (r["status"] === "success") {
            window.localStorage.setItem("AuthCat-QuickAuthToken", r["token"]);
            alert("Done!");
        }
        else {
            alert(r["message"]);
        }
    });
}

function sso_revoke_quick_auth(id) {
    let token = window.localStorage.getItem("AuthCat-QuickAuthToken");

    fetch("/sso/revoke-quick-auth.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            "id": id,
            "token": token === null ? undefined : token
        })
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") {
            alert("Done!");
        }
        else {
            alert("Failed! " + r.message);
        }
    });
}

function sso_upload_pfp(file) {
    let d = new FormData();
    d.append("file", file);

    fetch("https://cdn.nathcat.net/pfps/upload.php", {
        method: "POST",
        credentials: "include",
        body: d
    }).then((r) => r.json()).then((r) => {
        console.log(r);

        if (r.status === "fail") {
            alert(r.message);
        }
        else {
            window.location.search = "newPfpPath=" + r.name;
        }
    });
}