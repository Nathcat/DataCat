package net.nathcat.data;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStreamReader;

import com.google.gson.Gson;

public final class App {
  public static final String CONFIG_PATH = "Assets/Config.json";

  public static void main(String[] args) throws FileNotFoundException, IOException {
    Gson gson = new Gson();
    Config config = gson.fromJson(new InputStreamReader(new FileInputStream(CONFIG_PATH)), Config.class);

    Server server = new Server(config);
    server.start();
  }
}
