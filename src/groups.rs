mod db;
pub mod endpoints;

#[derive(serde::Serialize)]
pub struct Group {
    id: u32,
    name: String,
    owner: u32,
}
