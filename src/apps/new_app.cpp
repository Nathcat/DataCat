#include "AuthCat/client/auth.hpp"
#include "AuthCat/common.hpp"
#include "AuthCat/db/Credentials.hpp"
#include "DataCat/DataCat.hpp"
#include "jdbc/cppconn/connection.h"
#include "jdbc/cppconn/exception.h"
#include "jdbc/cppconn/prepared_statement.h"
#include <DataCat/Apps.hpp>
using namespace nathcat::data::apps;
using namespace nathcat::data;

void new_app(const httplib::Request &req, httplib::Response &res) {
  if (!util::assert_request_params(req, res, {"name"}))
    return;

  std::string name = req.get_param_value("name");

  std::string auth_token;
  nathcat::auth::User user;
  try {

    nathcat::auth::Credentials_Token token_creds(auth_token);
    user = nathcat::auth::authenticate(token_creds);

  } catch (nathcat::auth::AuthFailed) {
    util::handle_auth_failed(res);
  }

  std::unique_ptr<sql::Connection> db;

  try {
    util::open_db_connection(db, config.db.schema);

    std::unique_ptr<sql::PreparedStatement> stmt{
        db->prepareStatement("INSERT INTO Apps (`owner`, `name`, `apiKey`) "
                             "VALUES (?, ?, SHA2(UUID(), 256))")};

    stmt->setInt(1, user.id);
    stmt->setString(2, name);

    stmt->executeUpdate();
    stmt->close();
  } catch (sql::SQLException &e) {
    util::handle_sql_exception("new_app", e, res);
    return;
  }
}
