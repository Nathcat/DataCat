package net.nathcat.data.apps;

import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.sql.SQLException;
import java.util.Map;

import com.google.gson.Gson;
import com.sun.net.httpserver.HttpExchange;

import net.nathcat.api.Server;
import net.nathcat.api.handlers.ApiHandler;
import net.nathcat.authcat.User;
import net.nathcat.data.Utils;
import net.nathcat.sql.Query;

/**
 * Delete app endpoint
 *
 */
public final class Delete extends ApiHandler {

  public final static class Request {
    public int app;
  }

  protected Delete(Server server, String loggerName) {
    super(server, loggerName, new String[] { "POST" });
  }

  @Override
  public void handle(HttpExchange ex, User user, Map<String, String> params) throws IOException {
    // Get request body
    InputStream in = ex.getRequestBody();
    Gson gson = new Gson();
    Request request = gson.fromJson(
        new InputStreamReader(in), Request.class);

    try {
      // Check if the user is permitted to access DataCat
      if (!Utils.isPermitted(server.db, user) || !Utils.ownsApp(server.db, user, request.app)) {
        writeError(ex, 403);
        return;
      }

      // Delete the app record
      try {
        Query q = server.db.newQuery("DELETE FROM Apps WHERE `owner` = ? AND `id` = ?");
        q
            .set(1, int.class, user.id)
            .set(2, int.class, request.app)
            .executeUpdate();

        // Write success response to the client
        writeJson(ex, new SuccessResponse());

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
