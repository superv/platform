[![Build Status](https://travis-ci.org/superv/superv-platform.svg?branch=master)](https://travis-ci.org/superv/superv-platform)
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

### Addons
Every single reusable composer package in SuperV platform is called an `addon`. Different types of addons have different features. For now we will use the type `module` which is most common addon type.

SuperV groups all your platform related addon packages under the `addons` directory that is located at your project root. While installing through composer, it detects platform specific packages and moves them here instead of the default vendor folder.

Every SuperV addon has a unique slug combined of 3 parameters; `vendor.plural_addon_type.name`


#### Making your first module
Let's create a sample module to demonstrate the key features mentioned above. We will be creating a CRM module for our company ACME, thus our addon slug will be `acme.modules.crm`. Let's do this using the command line tool:

```bash
php artisan make:addon acme.modules.crm
```

You can now find the created module files in `addons/acme/modules/crm` directory.

Before using your addon, you must install it first:

```bash
php artisan addon:install acme.modules.crm
```

This would run the migrations located in your addon's `database/migrations` folder if any.

While developing an addon, you can use `addon:reinstall` command to uninstall and install again. And also `addon:uninstall` to uninstall it. 

Note that, uninstalling an addon rollbacks all it's migrations, thus would drop related database tables.

Now, let's create an addon migration:
```bash
php artisan addon:migration
```

We didn't provide an addon slug in this command, because the interactive command line will let us choose it among the installed addons.
For this migration I will be using `crm_clients` as the table name that I want to create.


As now, the new migration file should be created in `addons/acme/modules/crm/database/migrations` folder. Open it up in your IDE and paste the following:

```php
<?php

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class CreateCrmClientsTable extends Migration
{
    public function up()
    {
        Schema::create('crm_clients',
            function (Blueprint $table, ResourceConfig $resource) {
                $resource->label('Clients');
                $resource->nav('acp.crm.clients');
                $resource->resourceKey('client');
                $resource->entryLabel('{last_name}, {first_name}');

                $table->increments('id');
                $table->string('first_name');
                $table->string('last_name');

                $table->createdBy()->updatedBy();
                $table->restorable();
            });
    }

    public function down()
    {
        Schema::dropIfExists('crm_clients');
    }
}
```



## Contributing


## License
[MIT](https://github.com/superv/superv-platform/blob/master/LICENSE.md)
