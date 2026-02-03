package net.nathcat.ssl;

import javax.net.ssl.SSLContext;

public interface ISSLProvider {
  SSLContext getContext();
}
