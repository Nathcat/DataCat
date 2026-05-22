#ifndef DATACAT_AUTHCAT
#define DATACAT_AUTHCAT

#include "AuthProvider.hpp"

namespace nathcat {
namespace data {
namespace auth {
namespace authcat {

/**
 * @class User
 * @brief Encapsulation of an AuthCat user
 *
 */
class User : public nathcat::data::auth::User {
private:
  const std::string username;
  const std::string fullName;
  const std::string email;
  const std::string pfpPath;
  const std::string password;
  const bool verified;

public:
  User(int id, std::string username, std::string fullName, std::string email,
       std::string pfpPath, std::string password, bool verified)
      : nathcat::data::auth::User(id), username(username), fullName(fullName),
        email(email), pfpPath(pfpPath), password(password), verified(verified) {
  }

  const std::string getUsername() const { return username; }
  const std::string getFullName() const { return fullName; }
  const std::string getEmail() const { return email; }
  const std::string getPfpPath() const { return pfpPath; }
  const std::string getPassword() const { return password; }
  const bool getVerified() const { return verified; }
};

/**
 * @class Credentials
 * @brief Encapsulation of AuthCat credentials
 *
 */
class Credentials : public nathcat::data::auth::Credentials {
private:
  const std::string username;

public:
  Credentials(std::string username, std::string secret)
      : nathcat::data::auth::Credentials(secret), username(username) {}
};

class AuthProvider : nathcat::data::auth::AuthProvider<
                         nathcat::data::auth::authcat::User,
                         nathcat::data::auth::authcat::Credentials> {
  User getUser(const int id) override;
  User authenticate(const Credentials creds) override;
};

} // namespace authcat
} // namespace auth
} // namespace data
} // namespace nathcat

#endif
