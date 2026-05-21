#include <DataCat/DataCat.hpp>
using namespace nathcat::data;

void get(const httplib::Request &, httplib::Response &res) {
  res.set_content("Hello world, from nathcat's wrapper!", "text/plain");
}

int main() {
  Server s;

  s.registerEndpoint({"/", {get, nullptr}});

  s.listen("0.0.0.0", 8080);
}
