use crate::authcat::UserParamContainer;

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

impl UserParamContainer<i32> for LeaderboardRecord {
    fn get_param(&self) -> i32 {
        self.user
    }
}
