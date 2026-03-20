package net.nathcat.data.apps;

import net.nathcat.api.ContextPair;
import net.nathcat.api.Module;
import net.nathcat.api.ServerCommand;

public class LegacyModule implements Module {

  @Override
  public String basePath() {
    return "/data";
  }

  @Override
  public ContextPair[] contexts() {
    return new ContextPair[] {
        new ContextPair("new-app.php", New.class),
        new ContextPair("delete-app.php", Delete.class),
        new ContextPair("get-apps.php", Get.class)
    };
  }

  @Override
  public Class<? extends ServerCommand>[] getCommands() {
    return null;
  }

}
