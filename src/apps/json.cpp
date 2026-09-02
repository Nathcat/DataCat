#include <DataCat/Apps.hpp>

void nathcat::data::apps::to_json(nlohmann::json &j, const struct App &a) {
  j = {
      {"id", a.id}, {"owner", a.owner}, {"name", a.name}, {"apiKey", a.apiKey}};
}
