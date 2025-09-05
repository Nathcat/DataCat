function get_apps(success_callback, fail_callback) {
    fetch("/data/get-apps.php", {
        method: "GET",
        credentials: "include"
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback(r.results);
        else fail_callback(r.message);
    });
}

function new_app(name, success_callback, fail_callback) {
    fetch("/data/new-app.php", {
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
    fetch("/data/delete-app.php", {
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
    fetch("/data/get-leaderboards.php", {
        method: "GET",
        credentials: "include"
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback(r.results);
        else fail_callback(r.message);
    });
}

function new_leaderboard(name, appId, success_callback, fail_callback) {
    fetch("/data/new-leaderboard.php", {
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
    fetch("/data/delete-leaderboard.php", {
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

function add_webhook(name, url, leaderboard, api_key, success_callback, fail_callback) {
    fetch("https://data.nathcat.net/data/add-leaderboard-webhook.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            "name": name,
            "leaderboard": leaderboard,
            "apiKey": api_key,
            "url": url
        })
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback();
        else fail_callback(r.message);
    });
}

function delete_webhook(id, api_key, success_callback, fail_callback) {
    fetch("https://data.nathcat.net/data/delete-leaderboard-webhook.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            "id": id,
            "apiKey": api_key
        })
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback();
        else fail_callback(r.message);
    });;
}

function get_groups(success_callback, fail_callback) {
    fetch("https://data.nathcat.net/data/get-groups.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        credentials: "include"
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback(r);
        else fail_callback(r.message);
    });
}

function create_group(name, success_callback, fail_callback) {
    fetch("https://data.nathcat.net/data/get-groups.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            "name": name
        }),
        credentials: "include"
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback();
        else fail_callback(r.message);
    });
}