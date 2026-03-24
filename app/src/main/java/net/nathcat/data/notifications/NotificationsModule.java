package net.nathcat.data.notifications;

import net.nathcat.api.ContextPair;
import net.nathcat.api.Module;
import net.nathcat.api.ServerCommand;

public class NotificationsModule implements Module {

  @Override
  public String basePath() {
    return "/data/notifications";
  }

  @Override
  public ContextPair[] contexts() {
    return new ContextPair[] {
        new ContextPair("channel/get", GetChannels.class),
        new ContextPair("channel/register", RegisterNewChannel.class),
        new ContextPair("channel/targets/get", GetTargets.class),
        new ContextPair("channel/targets/register", RegisterNewChannelTarget.class)
    };
  }

  @Override
  public Class<? extends ServerCommand>[] getCommands() {
    return null;
  }

}
