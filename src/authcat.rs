use salvo::prelude::*;

use serde::Deserialize;
#[derive(Deserialize, Default, Clone, Debug)]
pub struct Config {
    auth_url: String,
    client_id: String
}
