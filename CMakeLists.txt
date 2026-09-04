cmake_minimum_required(VERSION 3.31.11)

add_subdirectory(libs)

project(DataCat)
add_executable(DataCat src/main.cpp)

target_include_directories(DataCat PUBLIC
include
libs/apiserver/include
libs/mysql/include
libs/json/include
libs/authcat/include
libs/httplib
)

target_link_libraries(DataCat PUBLIC
apiserver
nlohmann_json::nlohmann_json
AuthCat-Client
httplib
mysqlcppconn
)

target_sources(DataCat PUBLIC
src/config.cpp
src/json.cpp
src/util.cpp

src/apps/db.cpp
src/apps/json.cpp
src/apps/get_apps.cpp
src/apps/new_app.cpp
src/apps/delete_app.cpp
)

target_link_directories(DataCat PUBLIC
libs/kitty-committee/authcat
libs/nathcat/apiserver
libs/mysql/lib64
)
