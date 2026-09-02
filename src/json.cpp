#include <DataCat/DataCat.hpp>

namespace nathcat {
namespace data {
void from_json(const nlohmann::json &j, struct Config &c) {
  j.at("port").get_to(c.port);
}
} // namespace data
} // namespace nathcat
