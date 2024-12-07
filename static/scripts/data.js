function data_update_db_info() {

    $("#schemas-list").html("");

    let db_info_finish = () => {
        $(".db-record").each(function () {
            $(this).on("click", function (e) {
                $(this).toggleClass("minimised");
            });
        });
    };

    fetch("/run-query.php", {
        method: "POST",
        credentials: "include",
        body: "SHOW SCHEMAS;"
    }).then((r) => r.json()).then((r) => {
        if (r.status === "success") {
            let tables_query = "";
            let db_names = [];

            for (let i = 0; i < r.results[0].length; i++) {
                if (r.results[0][i].Database === "information_schema" || r.results[0][i].Database === "performance_schema") {
                    continue;
                }

                db_names.push(r.results[0][i].Database);
                tables_query += (i == 0 ? "" : "\n") + "USE `" + r.results[0][i].Database + "`;\n";
                tables_query += "SHOW TABLES;";
            }

            fetch("/run-query.php", {
                method: "POST",
                credentials: "include",
                body: tables_query
            }).then((f) => f.json()).then((f) => {
                if (f.status === "success") {
                    for (let i = 0; i < f.results.length; i++) {
                        let table_html = "";

                        if (f.results[i].length === 0) {
                            table_html = "<h4><i>No tables!</i></h4>";
                        }

                        for (let x = 0; x < f.results[i].length; x++) {
                            table_html += "<h4>" + f.results[i][x]["Tables_in_" + db_names[i]] + "</h4>";
                        }

                        document.getElementById("schemas-list").innerHTML += "<div class='db-record minimised'><h3>" + db_names[i] + "</h3>" + table_html + "</div>";
                    }

                    db_info_finish();
                }
                else {
                    alert(r.message);
                }
            });

        } else {
            alert(r.message);
        }
    });
}

function data_create_results_table(set) {
    if (set.length === 0) {
        return;
    }

    let keys = Object.keys(set[0]);
    let table_headers = "<tr>";
    for (let i = 0; i < keys.length; i++) {
        table_headers += "<th>" + keys[i] + "</th>";
    }

    table_headers += "</tr>";
    let table_content = "";

    for (let i = 0; i < set.length; i++) {
        table_content += "<tr>";

        for (let a = 0; a < keys.length; a++) {
            table_content += "<td>" + set[i][keys[a]] + "</td>"; 
        }

        table_content += "</tr>";
    }

    return "<table>" + table_headers + table_content + "</table>";
}