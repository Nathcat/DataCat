use std::collections::HashMap;

use mysql::{Pool, PooledConn};
use salvo::prelude::*;

use crate::{
    apps::require_app_auth,
    authcat::{User, UserNoEmail, UserParamContainer, get_users_by_param},
    errors::ApiError,
    leaderboards::{LeaderboardRecord, db},
    responses::{JsonObj, Ok},
};

#[derive(serde::Deserialize)]
struct NewLeaderboardRequest {
    name: String,
}

#[handler]
pub async fn new_leaderboard(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let db = depot.get_typed::<Pool>().unwrap();

    let conn = db.get_conn();
    if let Ok(mut conn) = conn {
        if let Some(v) = require_app_auth(&mut conn, req, res) {
            if v.len() == 0 {
                res.status_code(StatusCode::UNAUTHORIZED);
                return Err(ApiError::Unspecified());
            }

            let body: NewLeaderboardRequest;
            match req.parse_body::<NewLeaderboardRequest>().await {
                Err(e) => {
                    res.status_code(StatusCode::BAD_REQUEST);
                    return Err(ApiError::ParseError(e));
                }
                Ok(v) => body = v,
            }

            if let Err(e) = db::create_leaderboard(&mut conn, &body.name, &v[0].id) {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                Err(ApiError::SqlError(e))
            } else {
                Ok(Ok {})
            }
        } else {
            res.status_code(StatusCode::UNAUTHORIZED);
            Err(ApiError::Unspecified())
        }
    } else {
        res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
        Err(ApiError::SqlError(conn.unwrap_err()))
    }
}

#[derive(serde::Serialize)]
struct LeaderboardRecordResponse {
    user: UserNoEmail,
    value: i32,
}

#[handler]
pub async fn get_state(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<JsonObj<Vec<LeaderboardRecordResponse>>, ApiError> {
    let db = depot.get_typed::<Pool>().unwrap();

    let conn = db.get_conn();
    if let Ok(mut conn) = conn {
        let id: u32;
        if let Some(v) = req.param::<u32>("id") {
            id = v;
        } else {
            res.status_code(StatusCode::BAD_REQUEST);
            return Err(ApiError::BadRequest());
        }

        let ascending: bool = req.query::<bool>("ascending").unwrap_or(true);
        let limit: u32 = req.query::<u32>("limit").unwrap_or(10);

        match db::get_records(&mut conn, &id, &ascending, &limit) {
            Err(e) => Err(ApiError::SqlError(e)),
            Ok(v) => {
                let users =
                    get_users_by_param::<i32, LeaderboardRecord>(String::from("id"), &v).await;

                let mut values: HashMap<i32, i32> = HashMap::new();
                for record in v {
                    values.insert(record.user, record.value);
                }

                let mut result: Vec<LeaderboardRecordResponse> = Vec::new();

                for user_res in users.into_iter() {
                    if let Err(e) = user_res {
                        return Err(ApiError::Unspecified());
                    }

                    let user = user_res.unwrap();
                    if user.len() == 0 {
                        return Err(ApiError::NotFound());
                    }

                    result.push(LeaderboardRecordResponse {
                        user: user[0].clone(),
                        value: *values.get(&user[0].id).unwrap(),
                    })
                }
                Ok(JsonObj(result))
            }
        }
    } else {
        res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
        Err(ApiError::SqlError(conn.unwrap_err()))
    }
}

#[derive(serde::Deserialize)]
struct EditRecordRequest {
    user: i32,
    value: Option<i32>,
    increment: Option<bool>,
}

#[handler]
pub async fn edit_record(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let db = depot.get_typed::<Pool>().unwrap();

    let conn = db.get_conn();
    if let Ok(mut conn) = conn {
        if let Some(v) = require_app_auth(&mut conn, req, res) {
            if v.len() == 0 {
                res.status_code(StatusCode::UNAUTHORIZED);
                return Err(ApiError::Unspecified());
            }

            let leaderboard: u32;
            match req.param::<u32>("id") {
                None => {
                    res.status_code(StatusCode::BAD_REQUEST);
                    return Err(ApiError::BadRequest());
                }
                Some(v) => leaderboard = v,
            }

            let body: EditRecordRequest;
            match req.parse_body::<EditRecordRequest>().await {
                Err(e) => {
                    res.status_code(StatusCode::BAD_REQUEST);
                    return Err(ApiError::ParseError(e));
                }
                Ok(v) => body = v,
            }

            if let Err(e) = db::edit_record(
                &mut conn,
                &leaderboard,
                &body.user,
                &body.value,
                &body.increment,
            ) {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                Err(ApiError::SqlError(e))
            } else {
                Ok(Ok {})
            }
        } else {
            res.status_code(StatusCode::UNAUTHORIZED);
            Err(ApiError::Unspecified())
        }
    } else {
        res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
        Err(ApiError::SqlError(conn.unwrap_err()))
    }
}

#[handler]
pub async fn delete_leaderboard(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let db = depot.get_typed::<Pool>().unwrap();

    let conn = db.get_conn();
    if let Ok(mut conn) = conn {
        if let Some(v) = require_app_auth(&mut conn, req, res) {
            if v.len() == 0 {
                res.status_code(StatusCode::UNAUTHORIZED);
                return Err(ApiError::Unspecified());
            }

            let id: u32;
            if let Some(v) = req.param::<u32>("id") {
                id = v;
            } else {
                res.status_code(StatusCode::BAD_REQUEST);
                return Err(ApiError::BadRequest());
            }

            if let Err(e) = db::delete_leaderboard(&mut conn, &id, &v[0].id) {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                Err(ApiError::SqlError(e))
            } else {
                Ok(Ok {})
            }
        } else {
            res.status_code(StatusCode::UNAUTHORIZED);
            Err(ApiError::Unspecified())
        }
    } else {
        res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
        Err(ApiError::SqlError(conn.unwrap_err()))
    }
}
