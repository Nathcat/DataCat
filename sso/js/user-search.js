function user_search(username_entry, fullName_entry, results_container_name) {
    let username = document.getElementById(username_entry).value;
    let fullName = document.getElementById(fullName_entry).value;
    let body = new Object();
    if (username !== "") body.username = username;
    if (fullName !== "") body.fullName = fullName;

    fetch("/sso/user-search.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body)
    }).then((r) => r.json())
    .then((r) => {
        if(r.state === "fail") {
            alert("The search failed! " + r.message);
            return;
        }

        let results_container = document.getElementById(results_container_name);
        results_container.innerHTML = "";
        let results = r.results;
        for (const [key, value] of Object.entries(results)) {
            fetch("/sso/user-card.php?id=" + key)
            .then((r) => r.text())
            .then((r) => {
                results_container.innerHTML += r;
            });
        }
    });
} 
