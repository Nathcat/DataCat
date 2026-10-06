use std::error::Error;

use salvo::http::ParseError;
use salvo::prelude::*;
use salvo::{Writer, async_trait};
use thiserror::Error;

use crate::authcat::AuthError;

/// Describes an error which may occur in the API. This will relay text info to both the console and
/// the user, but you must set the status code in the request handler before returning!
#[derive(Error, Debug)]
pub enum ApiError {
    AuthError(#[from] AuthError),
    ParseError(#[from] ParseError),
    SqlError(#[from] mysql::error::Error),
    SerdeJsonError(#[from] serde_json::Error),
    NotImplemented(),
    BadRequest(),
    RemoteError(#[from] reqwest::Error),
    NotFound(),
    Unspecified(),
}

impl std::fmt::Display for ApiError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        write!(
            f,
            "ApiError::{}: {}",
            match self {
                ApiError::AuthError(_) => "AuthError",
                ApiError::ParseError(_) => "ParseError",
                ApiError::SqlError(_) => "SqlError",
                ApiError::SerdeJsonError(_) => "SerdeJsonError",
                ApiError::NotImplemented() => "NotImplemented",
                ApiError::BadRequest() => "BadRequest",
                ApiError::RemoteError(_) => "RemoteError",
                ApiError::NotFound() => "NotFound",
                ApiError::Unspecified() => "Unspecified",
            },
            match self.source() {
                Some(e) => e.to_string(),
                None => String::from("No source error"),
            }
        )
    }
}

#[async_trait]
impl Writer for ApiError {
    async fn write(self, req: &mut Request, depot: &mut Depot, res: &mut Response) {
        eprintln!("{}", self.to_string());

        if let ApiError::AuthError(_) = self {
            if let Err(e) = res.add_header("WWW-Authenticate", "Bearer", true) {
                eprintln!(
                    "Failed to set WWW-Authenticate header in response! {}",
                    e.to_string()
                );
            }
        }
        res.render(&self.to_string());
    }
}
