#include "jdbc/cppconn/resultset.h"
#include <DataCat/Apps.hpp>
#include <api/sql.hpp>
using namespace nathcat::data::apps;

template <>
struct App
nathcat::sqlwrapper::fromRow<struct App>(std::unique_ptr<sql::ResultSet> &rs) {
  return {rs->getInt("id"), rs->getInt("owner"), rs->getString("name"),
          rs->getString("apiKey")};
}
