use mysql::Pool;
use salvo::prelude::*;
use serde::Deserialize;

use crate::apps::{App, db};
use crate::authcat::{AuthCat, require_authenticated};
use crate::errors::ApiError;
use crate::responses::{JsonObj, Ok};

#[derive(Deserialize)]
struct NewAppRequest {
    name: String,
}

#[handler]
/// Handler for creating a new DataCat application, requires PUT method, and NewAppRequest body
///
pub async fn new_app(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db_pool = depot.get_typed::<Pool>().unwrap();
        let body: NewAppRequest;
        match req.parse_json::<NewAppRequest>().await {
            Ok(v) => body = v,
            Err(e) => {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                return Err(ApiError::ParseError(e));
            }
        }

        let conn = db_pool.get_conn();
        if let Ok(mut conn) = conn {
            if let Err(e) = db::create_app(&mut conn, &body.name, &user.id) {
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
pub async fn get_apps(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<JsonObj<Vec<App>>, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db_pool = depot.get_typed::<Pool>().unwrap();

        let conn = db_pool.get_conn();
        if let Ok(mut conn) = conn {
            match db::get_apps_owned_by(&mut conn, &user.id) {
                Err(e) => {
                    res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                    Err(ApiError::SqlError(e))
                }
                Ok(v) => Ok(JsonObj(v)),
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

#[derive(serde::Deserialize)]
struct DeleteAppRequest {
    id: u32,
}

#[handler]
pub async fn delete_app(
    req: &mut Request,
    res: &mut Response,
    depot: &mut Depot,
) -> Result<Ok, ApiError> {
    let authcat = depot.get_typed::<AuthCat>().unwrap();
    let auth = require_authenticated(req, res, authcat).await;

    if let Ok(user) = auth {
        let db = depot.get_typed::<Pool>().unwrap();

        let body: DeleteAppRequest;
        match req.parse_json::<DeleteAppRequest>().await {
            Ok(v) => body = v,
            Err(e) => {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                return Err(ApiError::ParseError(e));
            }
        }

        match db.get_conn() {
            Ok(mut conn) => {
                if let Err(error) = db::delete_app(&mut conn, &body.id, &user.id) {
                    res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                    Err(ApiError::SqlError(error))
                } else {
                    Ok(Ok {})
                }
            }
            Err(error) => {
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                Err(ApiError::SqlError(error))
            }
        }
    } else {
        res.status_code(StatusCode::UNAUTHORIZED);
        Err(ApiError::AuthError(auth.unwrap_err()))
    }
}
