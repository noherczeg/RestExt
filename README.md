RestExt
=======

A PHP REST Extension for the Laravel Framework.

THIS IS AN ALPHA SOFTWARE.

With this package you can easily create a REST API which:

+ can be versioned via route prefixes
+ supports authorization in controllers or inside methods via interface implementation
+ supports query string aware pagination with pagination links, and metadata
+ link generation (e.g. auto self, parent, pagination)
+ currently can produce JSON and XML responses that can be set in configuration, or overridden in methods
+ content negotiation via Accept header, config file or method settings

##Installation:

This package is based on composer, so if you have it installed you're pretty much halfway done already.

### Composer:

Download and install composer from `http://getcomposer.org/download/`

Add this to your project root's `composer.json` file:
```
{
    "require": {
        "noherczeg/restext": "dev-master"
    }
}
```
If you're done, just run a `php composer install`, and the package is ready to be used!

### Registering the package with Laravel 4 as a service:

Put the following in your `app/config/app.php` file under `providers` array:
```
'Noherczeg\RestExt\RestExtServiceProvider',
```

Adding the aliases of the facades is done in the same file under `aliases`:
```
'RestExt'         => 'Noherczeg\RestExt\Facades\RestExt',
'RestLinker'      => 'Noherczeg\RestExt\Facades\RestLinker',
'RestResponse'    => 'Noherczeg\RestExt\Facades\RestResponse',
```

Overriding the default configurations is done in a published config file. You can create it by typing:
```
$ php artisan config:publish noherczeg/restext
```

Optionally I have provided a default schema for a database Log table which can be migrated and used for database logging.
```
$ php artisan migrate --package=noherczeg/restext
```

##General Information:

This Package is not a simple Package with a Service, but a collection of tools which may help the develpoment of RESTful
web applications with the Laravel Framework. Currently it's coupled to it, so it can't be used on it's own, but some of
it's components can be replaced via implementing the provided interfaces or extending some classses.

### Entities/Models

It's advised to implement `Noherczeg\RestExt\Entities\ResourceEntity` in your models or you may extend a premade Entity
as well which already implements the interface above: `Noherczeg\RestExt\Entities\ResourceEloquentEntity`.

A ResourceEntity implementation adds the ability for your Models to:
+ self validate
+ generate pageable or normal collcetions with the same method call


### Repositories

It's a good practice to use repositories in you app, sice that way you can abstract away the dependency from any store,
or database implementation.

Just like above there is an interface in the package which comes to your aid: `Noherczeg\RestExt\Repository\CRUDRepository`,
and a  sample implementation which can be extended: `Noherczeg\RestExt\Repository\RestExtRepository`.

Implementing or extending CRUDRepository gives you your basic CRUD operations with pagination support as well.

### Services

The next level of abstraction is the service level. Currently there is no interface for services since not everyone uses
 them, so it won't be included.

### Extra features

As stated above this package contains tools other then data manipulation as well. These tools can be found in the extra
 folder of the package.

The general assumption is that you copy them into your _app/_ directory.

##### errors.php ([link](https://github.com/noherczeg/RestExt/blob/master/extra/errors.php))
The package is currently built with a mindset that follows the convention of catching exceptions and events outside of
 controllers, so in this file, you may find examples of some general error handler, like:

+ permission exceptions
+ 4xx errors
+ datastore errors
+ etc..

Usage: Add `require app_path().'/errors.php';` to the end of `app/start/global.php`

##### filters.php ([link](https://github.com/noherczeg/RestExt/blob/master/extra/filters.php))
In this file I have provided a few extra examples besides the default ones that could help in building a REST API.

+ localization handling with Accept Headers
+ authenticating with HTTP Basic Auth

Usage: Add `require app_path().'/filters.php';` to the end of `app/start/global.php`

##### logs.php ([link](https://github.com/noherczeg/RestExt/blob/master/extra/logs.php))
This is an example listener which logs everything into the database.

Usage: Add `require app_path().'/logs.php';` to the end of `app/start/global.php`

##### RootController.php ([link](https://github.com/noherczeg/RestExt/blob/master/extra/RootController.php))
This can be placed to anywhere where your controllers are and used as an entry point to your REST API. Since Level3 of the
Richardson Maturity Model requires HATEOAS support this is crucial for your app.

In the example there are a few features which show other package functionallity like link building, and the use of the
Authorization interface.

This controller only generates links, but since a Resources can have contents as well, if you'd like to you can provide
other data as well.

##### routes.php ([link](https://github.com/noherczeg/RestExt/blob/master/extra/routes.php))
Versioned routes with basic authentication. Uses the RootController example from above :)


# Guides

Since complete usage examples are quite big ones depending on how much I would like to show, these guides are available
separately in the following links:

+ [Starter Guide with simple models, Authorization, etc...](http://github.com/noherczeg/RestExt/blob/master/docs/starter.md)


##Dev's note:
If there is anything wrong with the package or any of it's contents, please let me know via pull requests or issues. I'm tracking this repo, and will do it forever, so you can reach me :)