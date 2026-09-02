#include "AuthCat/client/auth.hpp"
#include "AuthCat/common.hpp"
#include "AuthCat/db/Credentials.hpp"
#include "DataCat/DataCat.hpp"
#include "api/sql.hpp"
#include "jdbc/cppconn/connection.h"
#include "jdbc/cppconn/exception.h"
#include "jdbc/cppconn/prepared_statement.h"
#include <DataCat/Apps.hpp>
#include <string>
using namespace nathcat::data::apps;
using namespace nathcat::data;

void nathcat::data::apps::get_apps(const httplib::Request &req,
                                   httplib::Response &res) {
  std::string auth_token = util::get_auth_token(req);
  nathcat::auth::User user;
  try {

    nathcat::auth::Credentials_Token token_creds(auth_token);
    user = nathcat::auth::authenticate(token_creds);

  } catch (nathcat::auth::AuthFailed) {
    util::handle_auth_failed(res);
  }

  std::unique_ptr<sql::Connection> db;
  std::vector<struct App> apps;

  try {
    util::open_db_connection(db, config.db.schema);

    std::unique_ptr<sql::PreparedStatement> stmt{
        db->prepareStatement("SELECT * FROM Apps WHERE `owner` = ?")};

    stmt->setInt(1, user.id);

    std::unique_ptr<sql::ResultSet> rs{stmt->executeQuery()};

    stmt->close();

    apps = nathcat::sqlwrapper::toArray<struct App>(rs);
  } catch (sql::SQLException &e) {
    util::handle_sql_exception("get_apps", e, res);
    return;
  }

  res.status = httplib::StatusCode::OK_200;
  res.set_content(nlohmann::json(apps).dump(), "application/json");
}
