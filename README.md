[![Build Status](https://travis-ci.org/superv/superv-platform.svg?branch=0.8)](https://travis-ci.org/superv/superv-platform)
# SuperV Platform
‼️💥💥 This package is under heavy development ⛏ and may not be suitable for production use yet. 💥💥‼️

SuperV is a Laravel package that aims to be a rapid development platform.

## Key Features
- Panels, as many as you need; Out of the box SPA Control Panels (based on VueJS)
- Unique browser-like tabs feature on control panels, switch back and forth without closing pages to save work time.
- Migrations on streoids; create CRUD for your database table in migrations. No model or resource file required if you don't need them.
- Modularity, simple, easy and accessable and test ready; groupped reusable composer-based addon packages.
- Ports; seperation of api access based on different hostnames or even same hostname but different prefixes.
- Many more to be documented..


## Installation

### On an existing project
! soon !

### As a fresh project

Create project with composer in your terminal:
```bash
composer create-project superv/superv-project
```

Your web server should point to project's `public` folder for your hostname (eg: `superv.test`). Just as it would in a normal Laravel application.

Required directory permissions are also same with a normal laravel application with one exception, which is the `addons` folder. So make sure this folder is writable by your web server too.

Next, create a database and add your credentials to your `.env` file:

```text
DB_HOST=localhost
DB_DATABASE=superv
DB_USERNAME=superv
DB_PASSWORD=secret
```

While there, make sure that your hostname is correct:

```text
SV_HOSTNAME=superv.test
```

Generate your JWT token:
```bash
php artisan jwt:secret
```

And install SuperV:
```bash
php artisan superv:install
```

And create your user for base admin panel:
```bash
php artisan superv:user "Root User" root@superv.io --password=secret
```

Navigate to `http://superv.test/superv` using your browser and login with the credentials you entered in previous step.


## Usage
...coming


## Contributing


## License
[MIT](https://github.com/superv/superv-platform/blob/master/LICENSE.md)
