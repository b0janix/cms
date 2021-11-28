# Project instructions

## Technologies used

`Ubuntu 18.04.6 LTS` `PHP 8.0.12` `  MySQL Ver 14.14 Distrib 5.7.36, for Linux (x86_64) using  EditLine wrapper`
`Laravel Framework Lumen (8.3.1) (Laravel Components ^8.0)` `Composer version 2.1.6`
`@vue/cli 4.5.11` `vue@3.2.23` `vue-router@4.0.12`

## Additional packages used

1. `php-open-source-saver/jwt-auth: ^1.2` - I've used this one as a replacement of the well known
`tymon/jwt-auth` which unfortunately seems that it's not maintained anymore, and it doesn't support
PHP 8.0. It is a package for creating and revoking json web tokens (JWT tokens) for API authentication.
I used JWT tokens for some routes that insert or deactivate data from the database

2. `fruitcake/laravel-cors: ^2.0` - Adds CORS (Cross-Origin Resource Sharing) headers
3. `fakerphp/faker: ^1.9.1` - I've used it in the db seeding
4. `phpunit/phpunit: ^9.5.10` - I wrote unit tests extending the TestCase library

## General description

I wrote two applications, a simple frontend Vue 3  application that uses vue-router, and a backend API 
built with Lumen. The frontend application sends requests to almost all backend endpoints except for one I think 
(deletion of the post) but you can test that using tool like curl or application like Postman. I've also built a custom 
pagination, the rest of the application is pretty basic. For the backend part I wrote a code 
that is able to get all the comments and posts, it can apply filters. The results can be filtered 
by all db columns (the frontend offers filtering only by id), sort by all db columns, 
you can retrieve all the comments per post and the post for each comment, 
you can search for comment content related to certain post that contains certain word and ctr. I also wrote some
unit test located into the tests folder.

## How to start the projects?

At first, I was thinking to dockerize the applications but lacked the time for that. So to start the
backend api just execute the following command
`php artisan -S 127.0.0.1:8080 -t public`
The next step would be to create the database. I have some .env configuration setup

APP_NAME=Lumen
APP_ENV=local
APP_KEY=SgBaQGS7sjyTzTsJ5sFNHdG6OgjYdyyn
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_SLACK_WEBHOOK_URL=

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms
DB_TEST_DATABASE=test_cms
DB_USERNAME=bojan
DB_PASSWORD=Jana2014?

CACHE_DRIVER=file
QUEUE_CONNECTION=sync

PAGINATION_LIMIT=10
DEFAULT_PAGE=1

JWT_SECRET=LIpjg5nQbFtkh1yBALwYWg18csDexIHjQoWORVKO9DgyjXqYwqP6WIhTg7IEpuM3

as you can see I've also introduced some test database. The databse configuration is stored inside
the config/databse.php file. I have separate configuration/connection for the testing db. That connection is used inside
the `phpunit.xml` file ex. `<env name="DB_CONNECTION" value="testing"/>` So all the tests are executed against the test db.
Inside TestCase I use the DatabaseMigrations trait, so after running the tests all the tables are being dropped. 
It basically runs the command `php artisan migrate:reset`

The next step would be for you to create the production db and the test db. I've named them cms and test_cms

Then, after that and running the migrations, you will probably want to seed the database with this command

`php artisan db:seed`

The seeding of 8191 records varies between 700ms and 900ms :) 


After that you can probably start the fornt-end app. You would have to have npm and node.js installed.
My npm version is 7.3.0, my node.js version is v15.5.1. I think that for Vue 3 node version greater than 8 is 
required. You can start the app with the command
`npm run serve`

In order to run the tests you will have to execute `vendor/bin/phpunit`. 
I've written 24 tests which contain 54 assertions.
