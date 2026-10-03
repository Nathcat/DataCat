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
}

impl std::fmt::Display for ApiError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        write!(f, "ApiError: {}", self.to_string())
    }
}

#[async_trait]
impl Writer for ApiError {
    async fn write(self, req: &mut Request, depot: &mut Depot, res: &mut Response) {
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
