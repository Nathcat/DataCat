/**
 * @file Apps.hpp
 */

#ifndef APPS_HPP
#define APPS_HPP

#include "nlohmann/json.hpp"
#include <httplib.h>
#include <string>
namespace nathcat {
namespace data {
namespace apps {

struct App {
  int id;
  int owner;
  std::string name;
  std::string apiKey;
};

void to_json(nlohmann::json &j, const struct App &a);

void new_app(const httplib::Request &req, httplib::Response &res);
void delete_app(const httplib::Request &req, httplib::Response &res);
void get_apps(const httplib::Request &req, httplib::Response &res);

} // namespace apps
} // namespace data
} // namespace nathcat
#endif
