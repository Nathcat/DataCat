package net.nathcat.data.db;

import net.nathcat.sql.DBType;

public final class App implements DBType {
  public int id;
  public String name;
  public int owner;
  public String apiKey;
}
