use std::any::Any;

use salvo::http::ParseError;
use salvo::prelude::*;
use salvo::{Depot, Writer, async_trait};

use crate::errors::ApiError;

/// Simple Ok response with no body
pub struct Ok {}

#[async_trait]
impl Writer for Ok {
    async fn write(self, _req: &mut Request, _depot: &mut Depot, res: &mut Response) {
        res.status_code(StatusCode::OK);
    }
}

/// Wraps a Vec to be written as JSON in a response
pub struct JsonVec<T: serde::Serialize + std::marker::Send>(pub Vec<T>);

#[async_trait]
impl<T: serde::Serialize + std::marker::Send> Writer for JsonVec<T> {
    async fn write(self, _req: &mut Request, _depot: &mut Depot, res: &mut Response) {
        match serde_json::to_string(&self.0) {
            Ok(result) => {
                res.render(result);
                res.add_header("Content-Type", "application/json", true);
            }
            Err(error) => {
                let e = ApiError::SerdeJsonError(error);
                res.status_code(StatusCode::INTERNAL_SERVER_ERROR);
                e.write(_req, _depot, res);
            }
        }
    }
}
