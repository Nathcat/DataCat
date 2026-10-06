use mysql::{PooledConn, Statement, prelude::Queryable};

use crate::apps::App;

pub fn create_app(db: &mut PooledConn, name: &String, owner: &i32) -> Result<bool, mysql::Error> {
    let r =
        db.prep("INSERT INTO Apps (`owner`, `name`, `apiKey`) VALUES (?, ?, SHA2(UUID(), 256))");

    if let Ok(stmt) = r {
        match db.exec_drop(stmt, (owner, name)) {
            Err(e) => {
                return Err(e);
            }
            _ => {
                return Result::Ok(true);
            }
        }
    } else {
        return Result::Err(r.unwrap_err());
    }
}

pub fn get_apps_owned_by(db: &mut PooledConn, owner: &i32) -> Result<Vec<App>, mysql::Error> {
    let r = db.prep("SELECT * FROM Apps WHERE `owner` = ?");

    if let Ok(stmt) = r {
        return db.exec_map(stmt, (owner,), |(id, owner, name, apiKey)| App {
            id,
            owner,
            name,
            apiKey,
        });
    } else {
        return Err(r.unwrap_err());
    }
}

pub fn delete_app(db: &mut PooledConn, app: &u32, owner: &i32) -> Result<bool, mysql::Error> {
    let r = db.prep("DELETE FROM Apps WHERE `owner` = ? AND `id` = ?");

    if let Ok(stmt) = r {
        if let Err(error) = db.exec_drop(stmt, (owner, app)) {
            Err(error)
        } else {
            Ok(true)
        }
    } else {
        Err(r.unwrap_err())
    }
}
