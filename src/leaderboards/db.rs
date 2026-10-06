use mysql::{PooledConn, prelude::Queryable};

use crate::leaderboards::LeaderboardRecord;

pub fn create_leaderboard(
    db: &mut PooledConn,
    name: &String,
    app: &u32,
) -> Result<(), mysql::Error> {
    let r = db.prep("INSERT INTO `Leaderboards` (`name`, `app`) VALUES (?, ?)");

    if let Ok(stmt) = r {
        db.exec_drop(stmt, (name, app))
    } else {
        Err(r.unwrap_err())
    }
}

pub fn delete_leaderboard(db: &mut PooledConn, id: &u32, app: &u32) -> Result<(), mysql::Error> {
    let r = db.prep("DELETE FROM `Leaderboards` WHERE `id` = ? AND `app` = ?");

    if let Ok(stmt) = r {
        db.exec_drop(stmt, (id, app))
    } else {
        Err(r.unwrap_err())
    }
}

pub fn get_records(
    db: &mut PooledConn,
    leaderboard: &u32,
    ascending: &bool,
    limit: &u32,
) -> Result<Vec<LeaderboardRecord>, mysql::Error> {
    let mut query = String::from("SELECT * FROM `Leaderboards_Data` WHERE `leaderboard` = ? ");

    match ascending {
        true => query.push_str("ORDER BY `value` ASC "),
        false => query.push_str("ORDER BY `value` DESC "),
    }

    query.push_str("LIMIT ");
    query.push_str(&limit.to_string());

    let r = db.prep(query);

    if let Ok(stmt) = r {
        db.exec_map(stmt, (leaderboard,), |(id, leaderboard, user, value)| {
            LeaderboardRecord {
                id,
                leaderboard,
                user,
                value,
            }
        })
    } else {
        Err(r.unwrap_err())
    }
}

pub fn edit_record(
    db: &mut PooledConn,
    leaderboard: &u32,
    user: &i32,
    value: &Option<i32>,
    increment: &Option<bool>,
) -> Result<(), mysql::Error> {
    let mut query = String::from(
        "INSERT INTO `Leaderboards_Data` (`leaderboard`, `user`, `value`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = ",
    );

    if let Some(v) = increment {
        if v == &true {
            query.push_str("`value` + ?");
        } else {
            query.push_str("`value` - ?");
        }
    } else {
        query.push_str("?");
    }

    let r = db.prep(query);

    if let Ok(stmt) = r {
        db.exec_drop(
            stmt,
            (leaderboard, user, value.unwrap_or(1), value.unwrap_or(1)),
        )
    } else {
        Err(r.unwrap_err())
    }
}
