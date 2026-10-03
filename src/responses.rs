use std::any::Any;

use salvo::http::ParseError;
use salvo::prelude::*;
use salvo::{Depot, Writer, async_trait};

pub struct Ok {}

#[async_trait]
impl Writer for Ok {
    async fn write(self, _req: &mut Request, _depot: &mut Depot, res: &mut Response) {
        res.status_code(StatusCode::OK);
    }
}
