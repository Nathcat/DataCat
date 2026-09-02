/*
 * @file DataCat.hpp
 */

#ifndef DATACAT
#define DATACAT

#include <httplib.h>
#include <nlohmann/json.hpp>
namespace nathcat {
namespace data {

struct Config {
  int port;
};

void from_json(const nlohmann::json &j, struct Config &c);

extern struct Config config;

struct Config get_config(std::string path);

} // namespace data
} // namespace nathcat

#endif
