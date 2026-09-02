#include <DataCat/DataCat.hpp>
#include <resolv.h>

namespace nathcat {
namespace data {
void from_json(const nlohmann::json &j, struct Config &c) {
  j.at("port").get_to(c.port);
  j.at("db").get_to(c.db);
}

void from_json(const nlohmann::json &j, struct DbConfig &c) {
  j.at("uri").get_to(c.uri);
  j.at("username").get_to(c.username);
  j.at("password").get_to(c.password);
  j.at("schema").get_to(c.schema);
}
} // namespace data
} // namespace nathcat
