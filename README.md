
# SuperV Platform for Laravel [![Build Status](https://travis-ci.org/superv/platform.svg?branch=master)](https://travis-ci.org/superv/platform)

SuperV is a Laravel package that provides an SPA Admin Panel based on your migration files, without generating or needing additional files.

Please visit https://docs.superv.io for documentation

## Video Tutorials
- [Installation](https://youtu.be/3CDE7rjcfdk)
- [Creating Resources](https://youtu.be/osaqOtebj7Y)
- [Creating Resource Relations](https://youtu.be/mJdGPWZswCI)
- [Frontend SPA Overview]
- [Creating Modules]

## Installation

### Install a package to an existing project

Pull in the superV platform package:
```bash
composer require superv/platform 0.21.x-dev
```

Run the installer
```bash
php artisan superv:install
```

Installer will try to complete the following configurations for you:
- [Configure composer.json for the Merge Plugin](https://docs.superv.io/getting-started/Configuration.html#configure-composer-json-for-the-merge-plugin)
- [Create a full privileged User](https://docs.superv.io/getting-started/Configuration.html#create-a-full-privileged-user)
- [Create the Addons directory](https://docs.superv.io/getting-started/Configuration.html#create-the-addons-directory)


Pull the Admin Panel SPA addon:
```bash
composer require superv/acp 0.21.x-dev
```

And install the addon to enable it:
```bash
php artisan addon:install addons/superv/drops/acp
```


You can now navigate to `http://your-base-hostname/superv` using your browser and login with the user credentials you created during the installation process.


### Install as a new project

Create project with composer in your terminal:
```bash
composer create-project superv/superv-project 0.21.x-dev@dev
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


And install superV:
```bash
php artisan superv:install
```

You can now navigate to `http://your-base-hostname/superv` using your browser and login with the user credentials you created during the installation process.

## Support
If you any questions, feel free to contact me on [Twitter](https://twitter.com/daliselcuk).

## License
[MIT](https://github.com/superv/superv-platform/blob/master/LICENSE.md)
