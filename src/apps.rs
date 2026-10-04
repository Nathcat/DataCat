mod db;
pub mod endpoints;

#[derive(serde::Serialize)]
pub struct App {
    id: u32,
    owner: u32,
    name: String,
    apiKey: String,
}
