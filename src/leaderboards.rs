pub mod db;
pub mod endpoints;

#[derive(serde::Deserialize)]
pub struct Leaderboard {
    pub id: u32,
    pub app: u32,
    pub name: String,
}

#[derive(serde::Serialize)]
pub struct LeaderboardRecord {
    pub id: u32,
    pub leaderboard: u32,
    pub user: i32,
    pub value: i32,
}
