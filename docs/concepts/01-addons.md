# Addons
Every single reusable composer package in superV platform is called an `addon`

superV groups all your platform related addon packages under the `addons` directory that is located at your project root. While installing through composer, it detects platform specific packages and moves them here instead of the default vendor folder.  
  
Addons have a unique slug combined of 3 parameters; vendor, type and name. So if we are to create a Crm module under the vendor name Acme we would use:  

```
acme.modules.crm
```

<p class="hom">Please not that plural of addon type should be in the slug.</p>

### Addon Types
Different types of addons have different features. Valid addon types are:
- Module
- Drop
- Agent

For now we will use the type `module` which is most inclusive addon type.
 
### Creating an addon
Let's create a sampl addon of type `module` to demonstrate the key features mentioned above. We will be creating a CRM module for our company ACME, thus our addon slug will be `acme.modules.crm`. 

Let's do this using the command line tool:

```bash
php artisan make:addon acme.modules.crm
```

You can now find the created module files in `addons/acme/modules/crm` directory.

### Install an addon
Before using your addon, you must install it first:

```bash
php artisan addon:install acme.modules.crm
```

This would run the migrations located in your addon's `database/migrations` folder if any.

### Uninstall an addon
To remove an addon from system and drop all related tables and data you may run the command:

```bash
php artisan addon:uninstall
```
and select the addon from console input.

<p class="hey">Uninstalling an addon rollbacks all it's migrations, thus would drop related database tables.</p>

### Reinstall an addon
While developing an addon, you can use `addon:reinstall` command to uninstall and install again.


