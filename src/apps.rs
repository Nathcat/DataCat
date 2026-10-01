pub mod endpoints;
mod db;

struct App {
    id: u32,
    owner: u32,
    name: String,
    apiKey: String,
}
