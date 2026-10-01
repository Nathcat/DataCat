use std::process::exit;

use mysql::Pool;
use salvo::prelude::*;

pub mod config;
pub mod authcat;
pub mod db;
pub mod apps;


#[tokio::main]
async fn main () {
    tracing_subscriber::fmt::init();

    // Read dotenv into config struct
    let r = dotenvy::dotenv();
    match r.err() {
        Some(error) => {
            eprintln!("{}", error);
            exit(0);
        },
        _ => {}
    }
    
    let authcat_config = match config::init_config::<authcat::Config>() {
        Some(v) => v,
        _ => exit(1)
    };

    println!("{:#?}", authcat_config);

    let db_config = match config::init_config::<db::Config>() {
        Some(v) => v,
        _ => exit(1)
    };

    println!("{:#?}", db_config);

    // Create DB connection pool
    let db_pool = Pool::new(&db_config.db_url[..]).unwrap();

    // Init server
    let acceptor = TcpListener::new("127.0.0.1:8080").bind().await;

    let router = Router::new()
        .hoop(
            affix_state::inject(authcat_config)
                .inject(db_pool)
        );

    Server::new(acceptor).serve(router).await;
}

