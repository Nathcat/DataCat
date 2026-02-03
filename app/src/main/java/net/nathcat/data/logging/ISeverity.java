package net.nathcat.data.logging;

/**
 * Describes an arbitrary logging severity
 *
 */
public interface ISeverity {
  /**
   * Format a log message for this severity.
   *
   * @param msg The message to be logged
   */
  String format(String msg);
}
