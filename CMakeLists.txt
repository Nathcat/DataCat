cmake_minimum_required(VERSION 3.31.11)

add_subdirectory(libs)

project(DataCat)
add_executable(DataCat src/main.cpp)

target_include_directories(DataCat PUBLIC
libs/apiservser/include
libs/apiservser/libs/mysql/include
)

target_link_libraries(DataCat PUBLIC
apiserver
)
