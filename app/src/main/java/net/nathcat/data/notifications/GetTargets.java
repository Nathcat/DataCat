package net.nathcat.data.notifications;

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
import net.nathcat.data.db.NotificationChannel;
import net.nathcat.data.db.NotificationChannelField;
import net.nathcat.data.db.NotificationChannelTarget;
import net.nathcat.sql.Query;

/**
 * Get all targets registered by this user
 */
public class GetTargets extends ApiHandler {

  protected GetTargets(Server server, String loggerName) {
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

      // Get a list of channels
      NotificationChannelTarget[] channels;
      try {
        Query q = server.db.newQuery(
            "SELECT Notification_Channel_Targets.* FROM Notification_Channel_Targets JOIN Apps ON Apps.apiKey = Notification_Channel_Targets.app WHERE Apps.`owner` = ?");
        q.set(1, int.class, user.id)
            .execute();
        channels = net.nathcat.sql.Utils.extractResults(q.getResultSet(), NotificationChannelTarget.class);
        q.close();
      } catch (NoSuchMethodException | InstantiationException | IllegalAccessException | InvocationTargetException
          | NoSuchFieldException e) {
        throw new RuntimeException(e);
      }

      writeJson(ex, channels);

    } catch (SQLException e) {
      writeError(ex, 500);
    }
  }

}
