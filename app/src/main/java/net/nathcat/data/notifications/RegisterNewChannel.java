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
import net.nathcat.sql.Query;

/**
 * Register a new notification channel.
 * A notification channel describes the form that a notification should take.
 *
 */
public class RegisterNewChannel extends ApiHandler {

  protected RegisterNewChannel(Server server, String loggerName) {
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
      NotificationChannel request = gson.fromJson(new InputStreamReader(body), NotificationChannel.class);

      // Register the channel and its fields.
      try {
        Query q = server.db.newQuery("INSERT INTO Notification_Channels (name) VALUES (?)");
        int channelId = q.set(1, String.class, request.name).executeUpdate();
        q.close();

        for (NotificationChannelField field : request.fields) {
          q = server.db
              .newQuery("INSERT INTO Notification_Channel_Fields (channel, name, type) VALUES (?, ?, ?)");
          q.set(1, int.class, channelId)
              .set(2, String.class, field.name)
              .set(3, String.class, field.type)
              .executeUpdate();
          q.close();
        }

      } catch (NoSuchMethodException e) {
        throw new RuntimeException(e);
      }

      writeJson(ex, new SuccessResponse());

    } catch (SQLException e) {
      writeError(ex, 500);
    }
  }

}
