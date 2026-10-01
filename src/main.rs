use std::process::exit;

use mysql::Pool;
use salvo::prelude::*;

use crate::authcat::{AuthCat, AuthError, User, require_authenticated};

pub mod apps;
pub mod authcat;
pub mod config;
pub mod db;

#[handler]
async fn test(depot: &mut Depot, req: &mut Request, res: &mut Response) -> Result<User, AuthError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    require_authenticated(req, res, authcat).await
}

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
    let acceptor = TcpListener::new("127.0.0.1:8080").bind().await;

    let router = Router::new()
        .hoop(affix_state::inject(authcat).inject(db_pool))
        .push(Router::with_path("/test").get(test));

    Server::new(acceptor).serve(router).await;
}
