package net.nathcat.data;

import net.nathcat.ssl.configs.LetsEncryptConfig;

/**
 * The config structure for the application
 *
 */
public final class Config {
  public final static class ServerConfig {
    public int port;
    public boolean useSsl;
    public LetsEncryptConfig sslConfig;
  }

  public ServerConfig server;
}
