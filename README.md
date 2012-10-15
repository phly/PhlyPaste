PhlyPaste - ZF2 Pastebin Module
===============================

This is a module implementing a ZF2 pastebin.

Installation
------------

Install via composer:

```javascript
{
    "minimum-stability": "dev"
    "require": {
        "phly/phly-paste": "dev-master"
    }
}
```

To allow Markdown as a type of markup, you'll also need to install
`EdpMarkdown`. This can be done with the following:

```bash
cd vendor
git clone --recursive git://github.com/EvanDotPro/EdpMarkdown.git
```

Mongo Usage
-----------

To use Mongo as a backend, you will need to do several things.

First, add "phly/phly-mongo" as a `composer.json` requirement:

```javascript
{
    "minimum-stability": "dev"
    "require": {
        "phly/phly-paste": "dev-master",
        "phly/phly-mongo": "dev-master"
    }
}
```

After running `php composer.phar update`, you'll also need to configure your
application to use Mongo. One easy way to do this is in your site's primary
module (usually "Application"):

```php
namespace Application;

use PhlyMongo\MongoDbFactory;
use PhlyMongo\MongoCollectionFactory;
use PhlyPaste\MongoPasteService;

class Module
{
    public function getServiceConfig()
    {
        return array('factories' => array(
           'Paste\Mongo'           => 'PhlyMongo\MongoConnectionFactory',
           'Paste\MongoDb'         => new MongoDbFactory('site', 'Paste\Mongo'),
           'Paste\MongoCollection' => new MongoCollectionFactory('pastes', 'Paste\MongoDb'),
           'PhlyPaste\MongoService' => function ($services) {
               $collection = $services->get('Paste\MongoCollection');
               return new MongoPasteService($collection);
           },
        ));
    }
}
```

Alternately, you can simply configure a service returning a `MongoCollection`, 
and pass that to the `MongoPasteService` constructor.

Make sure to create indices on each of the "hash" and "timestamp" fields:

```php
// Create a unique index on the "hash" field
$collection->ensureIndex(array('hash' => 1), array('unique' => true));

// Create an index on "timestamp" descending
$collection->ensureIndex(array('timestamp' => -1));
```

You can do the above in your factory, if desired.
