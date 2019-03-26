# Migrations

## Creating

Now, let's create an addon migration:
```bash
php artisan addon:migration
```

We didn't provide an addon slug in this command, because the interactive command line will let us choose it among the installed addons.
For this migration I will be using `crm_clients` as the table name that I want to create.


As now, the new migration file should be created in `addons/acme/modules/crm/database/migrations` folder. Open it up in your IDE and paste the following:

```php
<?php

use superV\Platform\Domains\Database\Schema\Blueprint;
use superV\Platform\Domains\Database\Migrations\Migration;
use superV\Platform\Domains\Database\Schema\Schema;
use superV\Platform\Domains\Resource\ResourceConfig;

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

## Running
To run the pending migrations for an addon, type:

```bash
php artisan addon:migrate
```
and select the addon from the console input.

Or you directly run with:

```bash
php artisan addon:migrate --addon=acme.modules.crm
```
