#include "DataCat/DataCat.hpp"
#include "api/api.hpp"
#include <httplib.h>

int main() {
  nathcat::data::config = nathcat::data::get_config("Assets/server_conf.json");

  nathcat::api::Server server;

  server.registerEndpoints({});

  std::cout << "Starting server on http://localhost:"
            << nathcat::data::config.port << std::endl;
  server.listen("0.0.0.0", nathcat::data::config.port);
}
