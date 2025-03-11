function get_apps(success_callback, fail_callback) {
    fetch("/api/get-apps.php", {
        method: "GET",
        credentials: "include"
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback(r.results);
        else fail_callback(r.message);
    });
}

function new_app(name, success_callback, fail_callback) {
    fetch("/api/new-app.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        credentials: "include",
        body: JSON.stringify({"name": name})
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback();
        else fail_callback(r.message);
    });
}

function delete_app(name, success_callback, fail_callback) {
    fetch("/api/delete-app.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        credentials: "include",
        body: JSON.stringify({"app": name})
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback();
        else fail_callback(r.message);
    });
}

function get_leaderboards(success_callback, fail_callback) {
    fetch("/api/get-leaderboards.php", {
        method: "GET",
        credentials: "include"
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback(r.results);
        else fail_callback(r.message);
    });
}

function new_leaderboard(name, appId, success_callback, fail_callback) {
    fetch("/api/new-leaderboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        credentials: "include",
        body: JSON.stringify({"name": name, "app": appId})
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback();
        else fail_callback(r.message);
    });
}

function delete_leaderboard(name, success_callback, fail_callback) {
    fetch("/api/delete-leaderboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        credentials: "include",
        body: JSON.stringify({"leaderboard": name})
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback();
        else fail_callback(r.message);
    });
}