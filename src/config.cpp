#include "api/api.hpp"
#include "jdbc/mysql_driver.h"
#include <DataCat/DataCat.hpp>

namespace nathcat {
namespace data {
struct Config config{};
sql::Driver *driver = sql::mysql::get_mysql_driver_instance();

struct Config get_config(std::string path) {
  nlohmann::json j = nlohmann::json::parse(api::util::read_file(path));
  return j.get<struct Config>();
}

} // namespace data
} // namespace nathcat
