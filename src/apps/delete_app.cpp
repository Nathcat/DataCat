#include "AuthCat/client/auth.hpp"
#include "AuthCat/common.hpp"
#include "AuthCat/db/Credentials.hpp"
#include "DataCat/DataCat.hpp"
#include "jdbc/cppconn/connection.h"
#include "jdbc/cppconn/exception.h"
#include "jdbc/cppconn/prepared_statement.h"
#include <DataCat/Apps.hpp>
#include <string>
using namespace nathcat::data::apps;
using namespace nathcat::data;

void nathcat::data::apps::delete_app(const httplib::Request &req,
                                     httplib::Response &res) {
  if (!util::assert_request_params(req, res, {"app"}))
    return;

  int app = std::stoi(req.get_param_value("app"));

  std::string auth_token = util::get_auth_token(req);
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

    std::unique_ptr<sql::PreparedStatement> stmt{db->prepareStatement(
        "DELETE FROM Apps WHERE `owner` = ? AND `id` = ?")};

    stmt->setInt(1, user.id);
    stmt->setInt(2, app);

    stmt->executeUpdate();
    stmt->close();
  } catch (sql::SQLException &e) {
    util::handle_sql_exception("delete_app", e, res);
    return;
  }

  util::handle_ok(res);
}
