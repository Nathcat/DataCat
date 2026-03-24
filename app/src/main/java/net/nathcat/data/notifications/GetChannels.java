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
import net.nathcat.sql.Query;

/**
 * Get all notification channels
 */
public class GetChannels extends ApiHandler {

  protected GetChannels(Server server, String loggerName) {
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
      NotificationChannel[] channels;
      try {
        Query q = server.db.newQuery("SELECT * FROM Notification_Channels");
        q.execute();
        channels = net.nathcat.sql.Utils.extractResults(q.getResultSet(), NotificationChannel.class);
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
