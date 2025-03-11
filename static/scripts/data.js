function get_apps(success_callback, fail_callback) {
    fetch("/get-apps.php", {
        method: "GET",
        credentials: "include"
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") success_callback(r.results);
        else fail_callback(r.message);
    });
}

function new_app(name, success_callback, fail_callback) {
    fetch("/new-app.php", {
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