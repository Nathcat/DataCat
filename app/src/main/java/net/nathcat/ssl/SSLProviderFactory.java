package net.nathcat.ssl;

import java.lang.reflect.InvocationTargetException;

import net.nathcat.ssl.configs.Config;

public class SSLProviderFactory {
  /**
   * Get an SSL provider from its class name.
   *
   * @param providerName The name of the SSL provider class
   * @param config       The config for the provider
   * @return The SSL provider found. If the provider cannot be found, an exception
   *         will be thrown.
   * @throws ClassNotFoundException Thrown if the requested name cannot be found
   */
  public static ISSLProvider getProvider(String providerName, Config config)
      throws InstantiationException, IllegalAccessException, IllegalArgumentException, InvocationTargetException,
      NoSuchMethodException, SecurityException, ClassNotFoundException {
    return ISSLProvider.class.cast(Class.forName("net.nathcat.data.ssl." + providerName)
        .getDeclaredConstructor(config.getClass()).newInstance(config));
  }
}
