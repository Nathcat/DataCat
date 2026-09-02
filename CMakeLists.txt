cmake_minimum_required(VERSION 3.31.11)

add_subdirectory(libs)

project(DataCat)

add_library(DataCat SHARED
  src/Server.cpp
  src/AuthCat.cpp
)

target_include_directories(DataCat PUBLIC
  include
  libs/cpp-httplib
  libs/mysql/include
)

target_link_libraries(DataCat PUBLIC
  httplib
  connector
)

project(server)

add_executable(server src/main.cpp)

target_include_directories(server PUBLIC
  include
)

target_link_libraries(server PUBLIC
  DataCat
)

