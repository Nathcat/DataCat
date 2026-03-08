package net.nathcat.data;

import java.lang.reflect.InvocationTargetException;
import java.sql.SQLException;

import net.nathcat.authcat.User;
import net.nathcat.sql.DBType;
import net.nathcat.sql.Database;
import net.nathcat.sql.Query;

public final class Utils {
  private Utils() {
  }

  private class UserData implements DBType {
    public int id;
    public boolean is_permitted;
  }

  /**
   * Determine whether a user is permitted to access DataCat
   *
   */
  public static boolean isPermitted(Database db, User u) throws SQLException {
    return isPermitted(db, u.id);
  }

  /**
   * Determine whether a user is permitted to access DataCat
   *
   */
  public static boolean isPermitted(Database db, int u) throws SQLException {
    Query q;
    UserData[] results;

    try {
      q = db.newQuery("SELECT is_permitted FROM UserData WHERE id = ?");
      q
          .set(1, int.class, u)
          .execute();

      results = net.nathcat.sql.Utils.extractResults(q.getResultSet(), UserData.class);
    } catch (IllegalAccessException | NoSuchMethodException | InstantiationException | InvocationTargetException
        | NoSuchFieldException e) {
      throw new RuntimeException(e);
    }

    if (results.length != 1)
      return false;
    else
      return results[0].is_permitted;
  }
}
