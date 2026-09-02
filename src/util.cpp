#include <DataCat/DataCat.hpp>
#include <httplib.h>
using namespace nathcat::data;

namespace nathcat {
namespace data {
namespace util {
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
} // namespace util
} // namespace data
} // namespace nathcat
