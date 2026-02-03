package net.nathcat.data;

import java.io.IOException;
import java.net.InetSocketAddress;
import java.util.Scanner;
import java.util.concurrent.Executors;

import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLEngine;
import javax.net.ssl.SSLParameters;

import com.sun.net.httpserver.HttpServer;
import com.sun.net.httpserver.HttpsConfigurator;
import com.sun.net.httpserver.HttpsParameters;
import com.sun.net.httpserver.HttpsServer;

import net.nathcat.data.logging.Logger;
import net.nathcat.data.logging.Warning;
import net.nathcat.ssl.LetsEncryptProvider;

public class Server {
  private final Config config;
  private HttpServer http;
  private final Logger logger = new Logger("DataCat Server", System.out);
  private boolean running = false;

  public Server(Config config) {
    this.config = config;
  }

  public void start() throws IOException {
    logger.log("Server is starting...");

    // Setup SSL
    //
    //

    if (config.server.useSsl) {
      http = HttpsServer.create(new InetSocketAddress(config.server.port), 0);
      LetsEncryptProvider provider = new LetsEncryptProvider(config.server.sslConfig);
      SSLContext context = provider.getContext();

      ((HttpsServer) http).setHttpsConfigurator(new HttpsConfigurator(context) {
        public void configure(HttpsParameters params) {
          try {
            SSLEngine engine = context.createSSLEngine();
            params.setNeedClientAuth(false);
            params.setCipherSuites(engine.getEnabledCipherSuites());
            params.setProtocols(engine.getEnabledProtocols());
            SSLParameters p = context.getSupportedSSLParameters();
            params.setSSLParameters(p);
          } catch (Exception e) {
            logger.log(Warning.class, "Failed to configure HTTPS server! " + e);
          }
        }
      });

      logger.log("HTTPS server was created successfully.");
    } else {
      http = HttpServer.create(new InetSocketAddress(config.server.port), 0);
      logger.log(Warning.class, "HTTP server was created successfully, but SSL is not enabled!");
    }

    // Setup handlers
    //
    //

    http.setExecutor(Executors.newCachedThreadPool());
    http.start();

    // Start the command loop
    //
    //

    logger.log("Server has been started! Press 'h' + enter for a list of commands :3");
    running = true;
    Scanner in = new Scanner(System.in);

    while (running) {
      String c = in.nextLine();

      switch (c) {
        case "q" -> {
          running = false;
        }
        default -> {
          logger.log("Commands :3 \n\t'q' - Quit \n\t'h' - help");
        }
      }
    }

    // Shut down the server
    //
    //

    logger.log("Shutting down");
    in.close();
    http.stop(0);

    logger.log("Server has been stopped! Good bye :3");
  }
}
