use std::process::exit;

use mysql::Pool;
use salvo::prelude::*;

use crate::{
    apps::endpoints::{delete_app, get_apps, new_app},
    authcat::AuthCat,
    groups::endpoints::{
        create_group, delete_group, get_group_members, get_groups, invite_action, invite_to_group,
        leave_group,
    },
    leaderboards::endpoints::{delete_leaderboard, edit_record, get_state, new_leaderboard},
};

pub mod apps;
pub mod authcat;
pub mod config;
pub mod db;
pub mod errors;
pub mod groups;
pub mod leaderboards;
pub mod responses;

#[tokio::main]
async fn main() {
    tracing_subscriber::fmt::init();

    // Read dotenv into config struct
    let r = dotenvy::dotenv();
    match r.err() {
        Some(error) => {
            eprintln!("{}", error);
            exit(0);
        }
        _ => {}
    }

    let authcat = match config::init_config::<authcat::AuthCat>() {
        Some(v) => v,
        _ => exit(1),
    };

    println!("{:#?}", authcat);

    let db_config = match config::init_config::<db::Config>() {
        Some(v) => v,
        _ => exit(1),
    };

    println!("{:#?}", db_config);

    // Create DB connection pool
    let db_pool = Pool::new(&db_config.db_url[..]).unwrap();

    // Init server
    let acceptor = TcpListener::new("127.0.0.1:10000").bind().await;

    let router = Router::new()
        .hoop(affix_state::inject(authcat).inject(db_pool))
        .push(
            Router::with_path("api")
                .push(
                    Router::with_path("apps")
                        .put(new_app)
                        .get(get_apps)
                        .delete(delete_app),
                )
                .push(
                    Router::with_path("groups")
                        .put(create_group)
                        .get(get_groups)
                        .push(Router::with_path("invite").post(invite_action))
                        .push(
                            Router::with_path("{id}")
                                .delete(delete_group)
                                .push(
                                    Router::with_path("members")
                                        .get(get_group_members)
                                        .delete(leave_group),
                                )
                                .push(Router::with_path("invite").put(invite_to_group)),
                        ),
                )
                .push(
                    Router::with_path("leaderboards").put(new_leaderboard).push(
                        Router::with_path("{id}")
                            .get(get_state)
                            .post(edit_record)
                            .delete(delete_leaderboard),
                    ),
                ),
        );

    Server::new(acceptor).serve(router).await;
}
