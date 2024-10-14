<p align="center" style="font-size:30px;">C H E C A D O R</p>
<p align="center" style="font-size:10px; margin-top:-12px;">WEB APP</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Minimum requirements

- php 8.22
- MySQL 8
- NodeJS 20
- npm 9.8.0
- [Composer](https://getcomposer.org/)

### Frameworks

- [Laravel 11](https://laravel.com/docs/11.x)
- [VueJS 3.2.41](https://vuejs.org/guide/introduction.html)

## Setup

- Create `.env` file

`cp .env.example .env`

> Save your sanity and use an .env file for your environment variables.

- Runs the migrations to create the tables in the database

`php artisan migrate`

- In order to standardize catalogs, seeders were created

`php artisan db:seed`

- Install composer dependencies

`composer install`

- Install JavaScript dependencies

`npm install`

- Build JS assets (Production environment)

`npm run build`

## Data encryption

We encrypt personal data such as name, RFC and CURP so it is necessary to establish an encryption key.

`php artisan key:generate`

## API

An API has been created for the online applicant registration app, so it is necessary to obtain a JWT.

It is necessary to have a user in the system.

- Login

Don't forget to add `application/json` to the `Content-Type` to the request header.

`POST http://127.0.0.1/api/v1/auth/login`

*example of the application body request*

```
{
    "email": "usuario@fgjtam.gob.mx",
    "password": "password"
}
```

*Example response*

```
{
	"access_token": "accessToken",
	"token_type": "bearer",
	"expires_in": 15552000
}
```

To consult the endpoints see the file `routes/api.php`

## docker-compose.override

```yml
services:
  # mysql:
  #   image: mysql:8
  #   environment:
  #     MYSQL_ROOT_PASSWORD: StrongPassword!123
  #     MYSQL_DATABASE: checador
  #     MYSQL_USER: sistemas
  #     MYSQL_PASSWORD: sistemas
  #   ports:
  #     - "3306:3306"
  #   networks:
  #     - netchecador
  #   volumes:
  #     - mysql:/var/lib/mysql

  mongo:
    image: mongo
    restart: always
    networks:
      - netchecador
    environment:
      MONGO_INITDB_ROOT_USERNAME: root
      MONGO_INITDB_ROOT_PASSWORD: example

  mongo-express:
    image: mongo-express
    restart: always
    networks:
      - netchecador
    ports:
      - 8081:8081
    environment:
      ME_CONFIG_MONGODB_ADMINUSERNAME: root
      ME_CONFIG_MONGODB_ADMINPASSWORD: example
      ME_CONFIG_MONGODB_URL: mongodb://root:example@mongo:27017/
      ME_CONFIG_BASICAUTH: false

  mssql:
    image: mcr.microsoft.com/mssql/server:2019-latest
    environment:
      ACCEPT_EULA: "Y"
      MSSQL_SA_PASSWORD: "Password123+"
    ports:
      - "1433:1433"
    networks:
      - netchecador
    volumes:
      - mssql:/var/opt/mssql

volumes:
  mssql:
    driver: local
  # mysql:
  #   driver: local
```
