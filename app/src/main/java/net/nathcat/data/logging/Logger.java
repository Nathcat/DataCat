package net.nathcat.data.logging;

import java.io.OutputStream;

public class Logger {
  public final String name;
  /**
   * The default logging severity
   */
  public Class<? extends ISeverity> defaultSeverity;
  private final OutputStream os;

  /**
   * Create a new logger with default severiy of {@link Info}.
   *
   * @param name The name of the logger
   * @param out  The stream to write log messages to
   */
  public Logger(String name, OutputStream out) {
    this.name = name;
    this.os = out;
    defaultSeverity = Info.class;
  }

  /**
   * Create a new logger with the given default severity.
   *
   * @param name            The name of the logger
   * @param out             The stream to write log messages to
   * @param defaultSeverity The default severity of the new logger
   */
  public <T extends ISeverity> Logger(String name, OutputStream out, Class<T> defaultSeverity) {
    this(name, out);
    this.defaultSeverity = defaultSeverity;
  }

  /**
   * Logs a message at the given severity.
   *
   * @param severity The severity to log at
   * @param msg      The message to log
   */
  public <T extends ISeverity> void log(Class<T> severity, String msg) {
    try {
      ISeverity s = severity.getConstructor().newInstance();
      os.write((name + ": " + s.format(msg) + "\n").getBytes());
    } catch (Exception e) {
      throw new RuntimeException(e);
    }
  }

  /**
   * Logs a message with the default logging severity: {@link defaultSeverity}.
   *
   * @param msg The message to log.
   */
  public void log(String msg) {
    log(defaultSeverity, msg);
  }
}
