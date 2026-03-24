package net.nathcat.data.db;

import net.nathcat.sql.DBType;

public class NotificationChannelTarget implements DBType {
  public int channel;
  public String endpoint;
  public String app;
}
