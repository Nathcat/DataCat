#include "api/api.hpp"
#include <DataCat/DataCat.hpp>

namespace nathcat {
namespace data {
struct Config config{};

struct Config get_config(std::string path) {
  nlohmann::json j = nlohmann::json::parse(api::util::read_file(path));
  return j.get<struct Config>();
}

} // namespace data
} // namespace nathcat
