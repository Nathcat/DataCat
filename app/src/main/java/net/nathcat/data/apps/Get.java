package net.nathcat.data.apps;

import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.lang.reflect.InvocationTargetException;
import java.sql.SQLException;
import java.util.Map;

import com.google.gson.Gson;
import com.sun.net.httpserver.HttpExchange;

import net.nathcat.api.Server;
import net.nathcat.api.handlers.ApiHandler;
import net.nathcat.authcat.User;
import net.nathcat.data.Utils;
import net.nathcat.data.db.App;
import net.nathcat.sql.Query;

/**
 * Get apps endpoint
 *
 */
public final class Get extends ApiHandler {
  private class Response extends SuccessResponse {
    public App[] results;
  }

  protected Get(Server server, String loggerName) {
    super(server, loggerName, new String[] { "GET" });
  }

  @Override
  public void handle(HttpExchange ex, User user, Map<String, String> params) throws IOException {
    try {
      // Check if the user is permitted to access DataCat
      if (!Utils.isPermitted(server.db, user)) {
        writeError(ex, 403);
        return;
      }

      // Create the new app record
      try {
        Query q = server.db.newQuery("SELECT * FROM Apps WHERE `owner` = ?");
        q
            .set(1, int.class, user.id)
            .execute();

        App[] apps;
        try {
          apps = net.nathcat.sql.Utils.extractResults(q.getResultSet(), App.class);
        } catch (InstantiationException | IllegalAccessException | InvocationTargetException | NoSuchFieldException e) {
          throw new RuntimeException(e);
        }

        // Write result to the client
        Response r = new Response();
        r.results = apps;
        writeJson(ex, r);

      } catch (NoSuchMethodException e) {
        throw new RuntimeException(e);
      }
    } catch (SQLException e) {
      writeError(ex, 500);
      logger.log(net.nathcat.logging.Error.class, e.toString());
      e.printStackTrace();
    }
  }

}
