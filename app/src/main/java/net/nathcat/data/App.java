package net.nathcat.data;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStreamReader;
import java.sql.SQLException;

import com.google.gson.Gson;

import net.nathcat.api.Server;
import net.nathcat.api.config.ServerConfig;
import net.nathcat.data.apps.AppsModule;

public class App {
  public static final String CONFIG_PATH = "Assets/config.json";

  public static void main(String[] args) {
    Server server;

    try {
      Gson gson = new Gson();
      ServerConfig config = gson.fromJson(
          new InputStreamReader(new FileInputStream(CONFIG_PATH)),
          ServerConfig.class);

      server = new Server(config);
    } catch (SQLException | IOException e) {
      throw new RuntimeException(e);
    }

    server.registerModule(AppsModule.class);
    server.start();
  }
}
