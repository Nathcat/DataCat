#include "DataCat/DataCat.hpp"
#include "api/api.hpp"
#include <DataCat/Apps.hpp>
#include <httplib.h>
using namespace nathcat::data;

int main() {
  nathcat::data::config = nathcat::data::get_config("Assets/server_conf.json");

  nathcat::api::Server server;

  server.registerEndpoints({});

  std::cout << "Starting server on http://localhost:"
            << nathcat::data::config.port << std::endl;

  server.registerEndpoints(
      {{"/data/apps/get", {apps::get_apps, nullptr}},
       {"/data/apps/new", {nullptr, apps::new_app}},
       {"/data/apps/delete", {nullptr, apps::delete_app}}});

  server.listen("0.0.0.0", nathcat::data::config.port);
}
