# perm

[![Build Status](https://travis-ci.org/andrewsuzuki/perm.svg)](https://travis-ci.org/andrewsuzuki/perm)
[![Still Maintained](http://stillmaintained.com/andrewsuzuki/perm.png)](http://stillmaintained.com/andrewsuzuki/perm)

perm offers a simple way to save and retrieve "native" php configuration files in the filesystem.

For example, if writing a cms like [Vessel](https://github.com/hokeo/vessel), you might want to have the ability to save fast configuration values (like a site title or url) **perm**-anently from an admin interface. This is easy with perm.

## Requirements

* PHP 5.3+
* Laravel 4
* Composer

## Installation

perm installs with [Composer](https://getcomposer.org) through [Packagist](https://packagist.org/packages/andrewsuzuki/perm).

Add the following to your composer.json:

```JSON
{
	"require": {
		"andrewsuzuki/perm": "dev-master",
	}
}
```

Then run `composer update`.

Now add the following to your `providers` array in config/app.php:

```PHP
'Andrewsuzuki\Perm\PermServiceProvider',
```

Now add the facade alias to the `aliases` array:

```PHP
'Perm' => 'Andrewsuzuki\Perm\Facades\Perm',
```

And that's it.

If you'd like to load/save files from a config base directory other than app/config, publish the package's configuration:

```
php artisan config:publish hokeo/vessel
```

Then modify the base path in app/config/packages/andrewsuzuki/perm/config.php.

## Usage

* Load a config file, or mark a non-existing file/directory for creation. *chainable*
```PHP
// dot notation from base path (see above for configuration)
$perm = Perm::load('profile.andrew');
// ...or absolute path (no extension)
$perm = Perm::load('/path/to/file');
```
> If the file's directory does not exist, **it will be created**.

* Get a config value.
```PHP
$location   = $perm->get('location');
$location   = $perm->location; // for the first level, you can use magic properties
$first_name = $perm->get('name.first'); // use dot notation for nested values
```

* Get multiple values in one go (returns array).
```PHP
$locationAndFirstName = $perm->get(array('location', 'name.first'));
// or...
list($location, $firstName) = $perm->get(array('location', 'name.first'));
```

* Get all config values.
```PHP
$config = $perm->all();
```

* Set a value. *chainable*
```PHP
$perm->set('timezone', 'UTC');
// for the first level, you can use magic properties
$perm->timezone = 'UTC';
```

* Set a value if and only if it doesn't exist. *chainable*
```PHP
$perm->setIf('timezone', 'UTC');
```

* Set multiple values in one go. *chainable*
```PHP
$perm->set(array('timezone' => 'UTC', 'location' => 'Earth'))
```

* Check if a key exists.
```PHP
$exists = $perm->has('location'); // true/false
$exists = $perm->has('name.first'); // true/false
```

* Forget a key. *chainable*
```PHP
$perm->forget('location');
```

* Reset (forget all key/values). *chainable*
```PHP
$perm->reset();
```

* Save updated config. *chainable*
```PHP
$perm->save();
```

* Update current loaded filename (will **not** update loaded config). Filename basename must not contain dots. *chainable*
```PHP
$perm->setFilename('/path/to/new/file');
// then you might set some more values, then call ->save() again, etc...
```

### Method chaining

Chaining methods is an easy way to consolidate, and improve readability. You can combine any of the above methods marked as *chainable*. For example:

```PHP
Perm::load('profile.andrew')->set('name', 'Andrew')->forget('location')->save();
```

## Contributors

* [Andrew Suzuki](http://andrewsuzuki.com)

To contribute, please fork and submit a pull request. Otherwise, feel free to submit possible enhancements/issues on the [issues](https://github.com/andrewsuzuki/perm/issues) page.

## License

Vessel is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)