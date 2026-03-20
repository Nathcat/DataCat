package net.nathcat.data.apps;

import net.nathcat.api.ContextPair;
import net.nathcat.api.Module;
import net.nathcat.api.ServerCommand;

public final class AppsModule implements Module {

  @Override
  public String basePath() {
    return "/data/apps";
  }

  @Override
  public ContextPair[] contexts() {
    return new ContextPair[] {
        new ContextPair("new", New.class),
        new ContextPair("delete", Delete.class)
    };
  }

  @Override
  public Class<? extends ServerCommand>[] getCommands() {
    return null;
  }

}
