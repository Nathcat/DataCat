package net.nathcat.data.db;

import net.nathcat.sql.DBType;

public class NotificationChannel implements DBType {
  public int id;
  public String name;
  public NotificationChannelField[] fields;
}
