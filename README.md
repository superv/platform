
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
Install superV Platform package:
```bash
composer require superv/platform 0.20.x-dev
```


Add composer merge plugin configuration under the `extra` key in your project's `composer.json` file:
```json
    {
        "extra": {
            [...]
            "merge-plugin": {
                "include": [
                    "addons/*/*/*/composer.json"
                ],
                "recurse": true,
                "replace": false
            }
        }
    }
```

<div class="alert alert--info">
Composer packages for the superV addons are located in a special folder called `addons` instead of the default vendor directory.
</div>

Create the addons directory, and make it writable:
```bash
mkdir addons
chmod -Rf 777 addons
echo 'superv/*' > addons/.gitignore
```

Install superV
```bash
php artisan superv:install
```

Pull the Admin Panel SPA package:
```bash
composer require superv/acp 0.20.x-dev
```

And install the SPA addon:

```bash
php artisan addon:install addons/superv/drops/acp
```


Navigate to `http://your-base-hostname/superv` using your browser to login with the user credentials you created during the installation process.


## Support
If you any questions, feel free to contact me on [Twitter](https://twitter.com/daliselcuk).

## License
[MIT](https://github.com/superv/superv-platform/blob/master/LICENSE.md)
