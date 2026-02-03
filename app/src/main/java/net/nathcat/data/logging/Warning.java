package net.nathcat.data.logging;

public class Warning implements ISeverity {
  /**
   * Formats for an info log. The message will be in italics, and yellow, and
   * prefixed with
   * "WARN: "
   *
   * @param msg The message to format
   * @return The formatted message
   */
  @Override
  public String format(String msg) {
    return "WARN: \033[3;33m" + msg + "\033[0m";
  }
}
