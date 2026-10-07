use std::error::Error;

use mysql::Pool;
use salvo::prelude::*;

use crate::{
    authcat::{AuthCat, User, UserNoEmail, get_users_by_param, require_authenticated},
    errors::ApiError,
    groups::{Group, db},
    responses::{JsonObj, Ok},
};

#[derive(serde::Deserialize)]
struct CreateGroupRequest {
    name: String,
}

#[handler]
/// Create a new group owned by the authenticated user
pub async fn create_group(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db_pool = depot.get_typed::<Pool>().unwrap();
        let body: CreateGroupRequest;
        match req.parse_json::<CreateGroupRequest>().await {
            Ok(v) => body = v,
            Err(e) => {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                return Err(ApiError::ParseError(e));
            }
        }

        let conn = db_pool.get_conn();
        if let Ok(mut conn) = conn {
            if let Err(e) = db::create_group(&mut conn, &body.name, &user.id) {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                Err(ApiError::SqlError(e))
            } else {
                Ok(Ok {})
            }
        } else {
            res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
            Err(ApiError::SqlError(conn.unwrap_err()))
        }
    } else {
        res.status_code(StatusCode::UNAUTHORIZED);
        Err(ApiError::AuthError(auth.unwrap_err()))
    }
}

#[handler]
pub async fn delete_group(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db_pool = depot.get_typed::<Pool>().unwrap();

        let group: u32;
        match req.param::<u32>("id") {
            None => {
                res.status_code(StatusCode::BAD_REQUEST);
                return Err(ApiError::BadRequest());
            }
            Some(v) => group = v,
        }

        let conn = db_pool.get_conn();
        if let Ok(mut conn) = conn {
            if let Err(e) = db::delete_group(&mut conn, &group, &user.id) {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                Err(ApiError::SqlError(e))
            } else {
                Ok(Ok {})
            }
        } else {
            res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
            Err(ApiError::SqlError(conn.unwrap_err()))
        }
    } else {
        res.status_code(StatusCode::UNAUTHORIZED);
        Err(ApiError::AuthError(auth.unwrap_err()))
    }
}

#[derive(serde::Serialize)]
struct GetGroupsResponse {
    owned: Vec<Group>,
    member: Vec<Group>,
}

#[handler]
/// Get groups that the authenticated user owns, or is a member of
pub async fn get_groups(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<JsonObj<GetGroupsResponse>, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db_pool = depot.get_typed::<Pool>().unwrap();

        let conn = db_pool.get_conn();
        if let Ok(mut conn) = conn {
            match db::get_groups(&mut conn, &user.id) {
                Err(e) => {
                    res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                    Err(ApiError::SqlError(e))
                }
                Ok((owned, member)) => Ok(JsonObj(GetGroupsResponse { owned, member })),
            }
        } else {
            res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
            Err(ApiError::SqlError(conn.unwrap_err()))
        }
    } else {
        res.status_code(StatusCode::UNAUTHORIZED);
        Err(ApiError::AuthError(auth.unwrap_err()))
    }
}

#[handler]
/// Get the members of a group. Does not require authentication
pub async fn get_group_members(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<JsonObj<Vec<UserNoEmail>>, ApiError> {
    let db_pool = depot.get_typed::<Pool>().unwrap();

    let group: u32;
    match req.param::<u32>("id") {
        None => {
            res.status_code(StatusCode::BAD_REQUEST);
            return Err(ApiError::BadRequest());
        }
        Some(v) => group = v,
    }

    let conn = db_pool.get_conn();
    if let Ok(mut conn) = conn {
        match db::get_group_members(&mut conn, &group) {
            Err(e) => {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                Err(ApiError::SqlError(e))
            }
            Ok(v) => {
                let r = get_users_by_param::<u32, u32>(String::from("id"), &v).await;
                let mut users: Vec<UserNoEmail> = Vec::new();
                r.into_iter().into_iter().for_each(|result| match result {
                    Err(e) => eprintln!("{}", e.source().unwrap().to_string()),
                    Ok(u) => users.push(u[0].clone()),
                });

                Ok(JsonObj(users))
            }
        }
    } else {
        res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
        Err(ApiError::SqlError(conn.unwrap_err()))
    }
}

#[derive(serde::Deserialize)]
struct InviteRequest {
    username: String,
}

#[handler]
pub async fn invite_to_group(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db_pool = depot.get_typed::<Pool>().unwrap();

        let group: u32;
        match req.param::<u32>("id") {
            None => {
                res.status_code(StatusCode::BAD_REQUEST);
                return Err(ApiError::BadRequest());
            }
            Some(v) => group = v,
        }

        let body: InviteRequest;
        match req.parse_body::<InviteRequest>().await {
            Err(e) => {
                res.status_code(StatusCode::BAD_REQUEST);
                return Err(ApiError::ParseError(e));
            }
            Ok(v) => body = v,
        }

        let conn = db_pool.get_conn();
        if let Ok(mut conn) = conn {
            let owns_group: bool;
            match db::is_owner(&mut conn, &group, &user.id) {
                Err(e) => {
                    res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                    return Err(ApiError::SqlError(e));
                }
                Ok(v) => owns_group = v,
            }

            if !owns_group {
                res.status_code(StatusCode::UNAUTHORIZED);
                return Err(ApiError::BadRequest());
            }

            let users_with_username = get_users_by_param::<String, String>(
                String::from("username"),
                &vec![body.username],
            )
            .await;
            let mut invitees: Vec<UserNoEmail> = Vec::new();
            users_with_username
                .into_iter()
                .for_each(|result| match result {
                    Err(e) => eprintln!("{}", e.source().unwrap().to_string()),
                    Ok(mut u) => invitees.append(&mut u),
                });

            for invitee in invitees.into_iter() {
                if let Err(e) = db::invite_to_group(&mut conn, &group, &user.id, &invitee.id) {
                    res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                    return Err(ApiError::SqlError(e));
                }
            }

            Ok(Ok {})
        } else {
            res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
            Err(ApiError::SqlError(conn.unwrap_err()))
        }
    } else {
        res.status_code(StatusCode::UNAUTHORIZED);
        Err(ApiError::AuthError(auth.unwrap_err()))
    }
}

#[derive(serde::Deserialize)]
struct InviteActionRequest {
    action: String,
}

#[handler]
pub async fn invite_action(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db_pool = depot.get_typed::<Pool>().unwrap();

        let token: String;
        match req.query::<String>("token") {
            None => {
                res.status_code(StatusCode::BAD_REQUEST);
                return Err(ApiError::BadRequest());
            }
            Some(v) => token = v,
        }

        let body: InviteActionRequest;
        match req.parse_body::<InviteActionRequest>().await {
            Err(e) => {
                res.status_code(StatusCode::BAD_REQUEST);
                return Err(ApiError::ParseError(e));
            }
            Ok(v) => body = v,
        }

        let conn = db_pool.get_conn();
        if let Ok(mut conn) = conn {
            if body.action.to_lowercase() == "accept" {
                match db::accept_invite(&mut conn, &token, &user.id) {
                    Err(e) => {
                        res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                        Err(ApiError::SqlError(e))
                    }
                    Ok(_) => Ok(Ok {}),
                }
            } else if body.action.to_lowercase() == "decline" {
                match db::decline_invite(&mut conn, &token, &user.id) {
                    Err(e) => {
                        res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                        Err(ApiError::SqlError(e))
                    }
                    Ok(_) => Ok(Ok {}),
                }
            } else {
                res.status_code(StatusCode::BAD_REQUEST);
                Err(ApiError::BadRequest())
            }
        } else {
            res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
            Err(ApiError::SqlError(conn.unwrap_err()))
        }
    } else {
        res.status_code(StatusCode::UNAUTHORIZED);
        Err(ApiError::AuthError(auth.unwrap_err()))
    }
}

#[handler]
pub async fn leave_group(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db_pool = depot.get_typed::<Pool>().unwrap();
        let group: u32;
        match req.param("id") {
            None => {
                res.status_code(StatusCode::BAD_REQUEST);
                return Err(ApiError::BadRequest());
            }
            Some(v) => group = v,
        }

        let conn = db_pool.get_conn();
        if let Ok(mut conn) = conn {
            if let Err(e) = db::remove_from_group(&mut conn, &group, &user.id) {
                Err(ApiError::SqlError(e))
            } else {
                Ok(Ok {})
            }
        } else {
            res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
            Err(ApiError::SqlError(conn.unwrap_err()))
        }
    } else {
        res.status_code(StatusCode::UNAUTHORIZED);
        Err(ApiError::AuthError(auth.unwrap_err()))
    }
}
