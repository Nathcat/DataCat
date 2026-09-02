#include "AuthCat/common.hpp"
#include <AuthCat/client/auth.hpp>
#include <DataCat/DataCat.hpp>
#include <httplib.h>
#include <regex>
using namespace nathcat::data;

namespace nathcat {
namespace data {
namespace util {

std::regex auth_header_regex("Bearer (.*)");

void nathcat::data::util::open_db_connection(
    std::unique_ptr<sql::Connection> &db, std::string schema) {
  db = std::unique_ptr<sql::Connection>{nathcat::data::driver->connect(
      data::config.db.uri, data::config.db.username, data::config.db.password)};
  db->setSchema(schema);
}

void handle_sql_exception(std::string handler, sql::SQLException &e,
                          httplib::Response &res) {
  std::cerr << handler << ": SQL Exception! " << e.what() << std::endl;
  res.status = httplib::StatusCode::InternalServerError_500;
  res.set_content("500 - Internal error", "text/plain");
}

bool assert_request_params(const httplib::Request &req, httplib::Response &res,
                           std::vector<std::string> paramNames) {
  for (int i = 0; i < paramNames.size(); i++) {
    if (!req.has_param(paramNames[i])) {
      res.status = httplib::StatusCode::BadRequest_400;
      res.set_content(std::string("Missing parameter").append(paramNames[i]),
                      "text/plain");
      return false;
    }
  }

  return true;
}

std::string get_auth_token(const httplib::Request &req) {
  if (!req.has_header("Authorization"))
    throw nathcat::auth::AuthFailed();

  std::smatch m;
  std::string header = req.get_header_value("Authorization");
  if (std::regex_match(header, m, auth_header_regex)) {
    return m[1];
  } else
    throw auth::AuthFailed();
}

void handle_auth_failed(httplib::Response &res) {
  res.status = httplib::StatusCode::Unauthorized_401;
  res.set_header("WWW-Authenticate", "Bearer");
}

void handle_ok(httplib::Response &res) {
  res.status = httplib::StatusCode::OK_200;
}
} // namespace util
} // namespace data
} // namespace nathcat
