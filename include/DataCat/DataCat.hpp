#ifndef DATACAT
#define DATACAT

#include <httplib.h>
#include <string>
#include <vector>
namespace nathcat {
namespace data {

typedef void (*HandlerFunc)(const httplib::Request &, httplib::Response &);

/**
 * @class methodhandlers
 * @brief Contains a set of handlers for different HTTP methods.
 *
 */
struct methodhandlers {
  HandlerFunc get;
  HandlerFunc post;
};

/**
 * @class endpoint
 * @brief Represents a server endpoint
 *
 */
struct endpoint {
  /**
   * @brief The path at which the endpoint is accessible
   */
  std::string path;
  /**
   * @brief The handlers for this endpoint
   */
  struct methodhandlers handlers;
};

/**
 * @class Server
 * @brief Encapsulates an internal HTTP server with some QoL methods
 *
 */
class Server {
private:
  httplib::Server http;

public:
  /**
   * @brief Register a new endpoint to the server
   */
  void registerEndpoint(struct endpoint);

  /**
   * @brief Register a set of endpoints
   */
  void registerEndpoints(std::vector<struct endpoint>);

  /**
   * @brief Starts the server listening to requests
   *
   * @param host The host IP to listen on
   * @param port The port to listen on
   */
  void listen(std::string host, int port) { http.listen(host, port); }
};

} // namespace data
} // namespace nathcat

#endif
