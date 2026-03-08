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
 * New app endpoint
 *
 */
public final class New extends ApiHandler {

  public final static class Request {
    public String name;
  }

  protected New(Server server, String loggerName) {
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
      if (!Utils.isPermitted(server.db, user)) {
        writeError(ex, 403);
        return;
      }

      // Create the new app record
      try {
        Query q = server.db.newQuery("INSERT INTO Apps (`owner`, `name`, `apiKey`) VALUES (?, ?, SHA2(UUID(), 256))");
        q
            .set(1, int.class, user.id)
            .set(2, String.class, request.name)
            .executeUpdate();

        // Write Ok response to the client
        writeOk(ex);

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
