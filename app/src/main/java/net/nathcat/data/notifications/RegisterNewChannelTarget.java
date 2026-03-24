package net.nathcat.data.notifications;

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
import net.nathcat.data.db.NotificationChannel;
import net.nathcat.data.db.NotificationChannelField;
import net.nathcat.data.db.NotificationChannelTarget;
import net.nathcat.sql.Query;

/**
 * Register a new notification channel target.
 * This specifies the endpoint that a channel should target. Notably a channel
 * can be used
 * to target multiple endpoints.
 */
public class RegisterNewChannelTarget extends ApiHandler {

  protected RegisterNewChannelTarget(Server server, String loggerName) {
    super(server, loggerName, new String[] { "POST" });
  }

  @Override
  public void handle(HttpExchange ex, User user, Map<String, String> params) throws IOException {
    try {
      // Check if the user is permitted to access DataCat
      if (!Utils.isPermitted(server.db, user)) {
        writeError(ex, 403);
        return;
      }

      // Get the request
      Gson gson = new Gson();
      InputStream body = ex.getRequestBody();
      NotificationChannelTarget request = gson.fromJson(new InputStreamReader(body), NotificationChannelTarget.class);

      if (request.app == null || request.endpoint == null) {
        writeError(ex, 400);
        return;
      }

      if (!Utils.ownsApiKey(server.db, user.id, request.app)) {
        writeError(ex, 403);
        return;
      }

      try {
        Query q = server.db
            .newQuery("INSERT INTO Notification_Channel_Targets (channel, endpoint, app) VALUES (?, ?, ?)");
        q.set(1, int.class, request.channel)
            .set(2, String.class, request.endpoint)
            .set(3, String.class, request.app)
            .executeUpdate();
        q.close();
      } catch (NoSuchMethodException e) {
        throw new RuntimeException(e);
      }

      writeJson(ex, new SuccessResponse());

    } catch (SQLException e) {
      writeError(ex, 500);
    }
  }

}
