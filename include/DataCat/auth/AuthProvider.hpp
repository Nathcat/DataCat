#ifndef DATACAT_AUTHPROVIDER
#define DATACAT_AUTHPROVIDER

#include <string>

namespace nathcat {
namespace data {
namespace auth {

/**
 * @class User
 * @brief Simple base user class
 *
 */
class User {
private:
  const int id;

public:
  User(int id) : id(id) {}

  const int getId() const { return id; }
};

class Credentials {
private:
  const std::string secret;

public:
  Credentials(std::string secret) : secret(secret) {}

  const std::string getSecret() const { return secret; }
};

/**
 * @brief Base definition of an authentication provider interface.
 *
 * @tparam U_type A type describing a user.
 * @tparam C_type A type describing a credential set.
 */
template <typename U_type, typename C_type> class AuthProvider {
public:
  /**
   * @brief Get a user from the authenitcation service from their unique ID
   *
   * @param id The user's unique ID
   * @return The data of the user with this unique ID.
   */
  virtual U_type getUser(const int id) = 0;

  /**
   * @brief Attempt to authenticate a user with a given set of credentials
   *
   * @param creds The credential set to use
   * @return The user linked to these credentials
   */
  virtual U_type authenticate(const C_type creds) = 0;
};
} // namespace auth
} // namespace data
} // namespace nathcat

#endif
