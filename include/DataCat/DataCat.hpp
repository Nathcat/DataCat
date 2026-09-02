/*
 * @file DataCat.hpp
 */

#ifndef DATACAT
#define DATACAT

#include "jdbc/cppconn/connection.h"
#include "jdbc/cppconn/driver.h"
#include <httplib.h>
#include <memory>
#include <nlohmann/json.hpp>
namespace nathcat {
namespace data {

struct DbConfig {
  std::string uri;
  std::string username;
  std::string password;
  std::string schema;
};

void from_json(const nlohmann::json &j, struct DbConfig &c);

struct Config {
  int port;
  struct DbConfig db;
};

void from_json(const nlohmann::json &j, struct Config &c);

extern struct Config config;
extern sql::Driver *driver;

struct Config get_config(std::string path);

namespace util {
/**
 * @brief Open a connect to the database specified in the program's config
 *
 * @param db The database connection to populate
 * @param schema The schema of the database to use
 */
void open_db_connection(std::unique_ptr<sql::Connection> &db,
                        std::string schema);

/**
 * @brief Handle an SQL exception in an endpoint
 *
 * @param handler The handler name, this will be prepended to the log message.
 * @param e The exception
 */
void handle_sql_exception(std::string handler, sql::SQLException &e,
                          httplib::Response &res);

/**
 * @brief Assert that a request must contain a set of specified parameters. This
 * function does not check form or type of the parameters, just that they exist.
 *
 * @param paramNames The names of the required parameters
 * @return true if all parameters are present, false if not
 */
bool assert_request_params(const httplib::Request &req, httplib::Response &res,
                           std::vector<std::string> paramNames);
} // namespace util

} // namespace data
} // namespace nathcat

#endif
