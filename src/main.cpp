#include "DataCat/DataCat.hpp"
#include <httplib.h>

int main() {
  nathcat::data::config = nathcat::data::get_config("Assets/server_conf.json");

  std::cout << "Config port is " << nathcat::data::config.port << std::endl;
}
