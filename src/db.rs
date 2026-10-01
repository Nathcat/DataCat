use serde::Deserialize;
#[derive(Deserialize, Default, Clone, Debug)]
pub struct Config {
    pub db_url: String
}


