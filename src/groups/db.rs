use mysql::{PooledConn, prelude::Queryable};

use crate::{authcat::User, groups::Group};

/// Create a group
pub fn create_group(db: &mut PooledConn, name: &String, owner: &i32) -> Result<bool, mysql::Error> {
    let r = db.prep("INSERT INTO `Groups` (`owner`, `name`) VALUES (?, ?)");

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

/// Delete a group, note that you must own a group to delete it
pub fn delete_group(db: &mut PooledConn, id: &u32, owner: &i32) -> Result<bool, mysql::Error> {
    let r = db.prep("DELETE FROM `Groups` WHERE `id` = ? AND `owner` = ?");

    if let Ok(stmt) = r {
        match db.exec_drop(stmt, (id, owner)) {
            Err(e) => Err(e),
            _ => Ok(true),
        }
    } else {
        Err(r.unwrap_err())
    }
}

/// Get the groups a user owns, and is a member of
pub fn get_groups(
    db: &mut PooledConn,
    user: &i32,
) -> Result<(Vec<Group>, Vec<Group>), mysql::Error> {
    let mut r = db.prep("SELECT * FROM `Groups` WHERE `owner` = ?");
    let owned: Vec<Group>;

    if let Ok(stmt) = r {
        match db.exec_map(stmt, (user,), |(id, name, owner)| Group { id, name, owner }) {
            Err(e) => return Err(e),
            Ok(v) => owned = v,
        }
    } else {
        return Err(r.unwrap_err());
    }

    r = db.prep("SELECT `Groups`.* FROM `Group_Members` JOIN `Groups` ON `Group_Members`.`group` = `Groups`.`id` WHERE `Group_Members`.`user` = ?");
    let member: Vec<Group>;

    if let Ok(stmt) = r {
        match db.exec_map(stmt, (user,), |(id, name, owner)| Group { id, name, owner }) {
            Err(e) => return Err(e),
            Ok(v) => member = v,
        }
    } else {
        return Err(r.unwrap_err());
    }

    Ok((owned, member))
}

/// Get the members of a group
pub fn get_group_members(db: &mut PooledConn, group: &u32) -> Result<Vec<u32>, mysql::Error> {
    let r = db.prep("SELECT `user` FROM `Group_Members` where `group` = ?");

    if let Ok(stmt) = r {
        match db.exec(stmt, (group,)) {
            Err(e) => Err(e),
            Ok(v) => Ok(v),
        }
    } else {
        Err(r.unwrap_err())
    }
}

/// Verify whether the given user is the owner of the given group
pub fn is_owner(db: &mut PooledConn, group: &u32, user: &i32) -> Result<bool, mysql::Error> {
    let r = db.prep("SELECT count(*) FROM `Groups` WHERE `id` = ? AND `owner` = ?");

    if let Ok(stmt) = r {
        let res: Result<Option<i32>, mysql::Error> = db.exec_first(stmt, (group, user));
        match res {
            Err(e) => Err(e),
            Ok(v) => {
                if let Some(r) = v {
                    return Ok(r != 0);
                } else {
                    return Ok(false);
                }
            }
        }
    } else {
        Err(r.unwrap_err())
    }
}

pub fn invite_to_group(
    db: &mut PooledConn,
    group: &u32,
    inviter: &i32,
    invitee: &i32,
) -> Result<bool, mysql::Error> {
    let r = db.prep("CALL invite_to_group(?, ?, ?)");

    if let Ok(stmt) = r {
        match db.exec_drop(stmt, (group, inviter, invitee)) {
            Err(e) => Err(e),
            Ok(_) => Ok(true),
        }
    } else {
        Err(r.unwrap_err())
    }
}

pub fn accept_invite(
    db: &mut PooledConn,
    token: &String,
    user: &i32,
) -> Result<bool, mysql::Error> {
    let r = db.prep("CALL accept_group_invite(?, ?)");

    if let Ok(stmt) = r {
        match db.exec_drop(stmt, (token, user)) {
            Err(e) => Err(e),
            Ok(_) => Ok(true),
        }
    } else {
        Err(r.unwrap_err())
    }
}

pub fn decline_invite(
    db: &mut PooledConn,
    token: &String,
    user: &i32,
) -> Result<bool, mysql::Error> {
    let r = db.prep("CALL decline_group_invite(?, ?)");

    if let Ok(stmt) = r {
        match db.exec_drop(stmt, (token, user)) {
            Err(e) => Err(e),
            Ok(_) => Ok(true),
        }
    } else {
        Err(r.unwrap_err())
    }
}

pub fn remove_from_group(
    db: &mut PooledConn,
    group: &u32,
    user: &i32,
) -> Result<bool, mysql::Error> {
    let r = db.prep("DELETE FROM `Group_Members` WHERE `group` = ? AND `user` = ?");

    if let Ok(stmt) = r {
        match db.exec_drop(stmt, (group, user)) {
            Err(e) => Err(e),
            Ok(_) => Ok(true),
        }
    } else {
        Err(r.unwrap_err())
    }
}
