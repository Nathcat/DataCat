#include "api/api.hpp"
#include <httplib.h>

void f(const httplib::Request &req, httplib::Response &res) {
  res.status = httplib::StatusCode::OK_200;
  res.set_content("Hello world :3", "text/plain");
}

int main() {
  nathcat::api::Server server;
  server.registerEndpoint({"/", {f, nullptr}});

  server.listen("0.0.0.0", 9090);
}
