mod db;
pub mod endpoints;

struct App {
    id: u32,
    owner: u32,
    name: String,
    apiKey: String,
}
