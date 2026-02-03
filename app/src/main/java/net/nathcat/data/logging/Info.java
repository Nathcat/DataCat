package net.nathcat.data.logging;

public class Info implements ISeverity {
  /**
   * Formats for an info log. The message will be in italics, and prefixed with
   * "INFO: "
   *
   * @param msg The message to format
   * @return The formatted message
   */
  @Override
  public String format(String msg) {
    return "INFO: \033[3m" + msg + "\033[0m";
  }
}
