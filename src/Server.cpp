#include <DataCat/DataCat.hpp>
using namespace nathcat::data;

void nathcat::data::Server::registerEndpoint(struct endpoint e) {
  if (e.handlers.get != nullptr) {
    http.Get(e.path, e.handlers.get);
  }

  if (e.handlers.post != nullptr) {
    http.Post(e.path, e.handlers.post);
  }
}

void nathcat::data::Server::registerEndpoints(
    std::vector<struct endpoint> endpoints) {
  for (struct endpoint e : endpoints) {
    registerEndpoint(e);
  }
}
