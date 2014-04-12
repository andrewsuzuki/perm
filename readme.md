# perm

[![Build Status](https://travis-ci.org/andrewsuzuki/perm.svg)](https://travis-ci.org/andrewsuzuki/perm)
[![Still Maintained](http://stillmaintained.com/andrewsuzuki/perm.png)](http://stillmaintained.com/andrewsuzuki/perm)

perm offers a simple way to save and retrieve "native" php configuration files in the filesystem.

For example, if writing a cms like [Vessel](https://github.com/hokeo/vessel), you can save your site title **perm**anently from an admin interface with perm.

## Requirements

* PHP 5.3+
* Laravel 4

## Installation

Add the following to your composer.json:

```
"require": {
    "andrewsuzuki/perm": "dev-master",
}
```

Then run `composer update`.

Now add the following to your `providers` array in config/app.php:

```
'Andrewsuzuki\Perm\PermServiceProvider',
```

Now add the facade alias to the `aliases` array:

```
'Perm' => 'Andrewsuzuki\Perm\Facades\Perm',
```

And that's it.


## Usage

* Load a config file, or mark a non-existing file for creation. *chainable*
```
$perm = Perm::load('/path/to/file'); (filename basename must not contain dots)
```
> If the file's directory does not exist, **it will be created**.

* Get a config value.
```
$location   = $perm->get('location');
$first_name = $perm->get('name.first'); // use dot notation for nested values
```

* Get multiple values in one go (returns array).
```
$locationAndFirstName = $perm->get(array('location', 'name.first'));
```

* Get all config values.
```
$config = $perm->getAll();
```

* Set a value. *chainable*
```
$perm->set('timezone', 'UTC');
```

* Set multiple values in one go. *chainable*
```
$perm->set(array('timezone' => 'UTC', 'location' => 'Earth'))
```

* Forget a key. *chainable*
```
$perm->forget('location');
```

* Save updated config. *chainable*
```
$perm->save();
```

* Update current loaded filename (will **not** update loaded config). Filename basename must not contain dots. *chainable*
```
$perm->setFilename('/path/to/new/file');
// then you might set some more values, then call ->save() again, etc...
```

### Method chaining

Chaining methods is an easy way to consolidate, and improve readability. You can combine any of the above methods marked as *chainable*. For example:

```
Perm::load(app_path('config').'/profile')->set('name', 'Andrew')->forget('location')->save();
```