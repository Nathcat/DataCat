use mysql::PooledConn;
use regex::regex;
use reqwest::StatusCode;
use salvo::{Request, Response};

use crate::apps::db::get_app;

mod db;
pub mod endpoints;

#[derive(serde::Serialize)]
pub struct App {
    pub id: u32,
    pub owner: u32,
    pub name: String,
    pub apiKey: String,
}

pub fn require_app_auth(
    db: &mut PooledConn,
    req: &mut Request,
    res: &mut Response,
) -> Option<Vec<App>> {
    let header: String;
    match req.header::<String>("Authorization") {
        None => {
            res.status_code(StatusCode::UNAUTHORIZED);
            return None;
        }
        Some(v) => header = v,
    }

    if let Some(caps) = regex!(r"Basic (?<id>.*):(?<api_key>.*)").captures(&header) {
        let id: u32;
        if let Ok(v) = caps["id"].to_owned().parse::<u32>() {
            id = v;
        } else {
            res.status_code(StatusCode::BAD_REQUEST);
            return None;
        }

        let api_key = caps["api_key"].to_owned();

        match get_app(db, &id, &api_key) {
            Err(e) => {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                eprintln!("Failed to get app: {}", e.to_string());
                None
            }
            Ok(v) => Some(v),
        }
    } else {
        None
    }
}
