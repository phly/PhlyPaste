PhlyPaste - ZF2 Pastebin Module
===============================

This is a module implementing a ZF2 pastebin.

Features
--------

- Normal pastebin features: syntax highlighting by language, short URLs, ability
  to mark pastes as "private" (meaning they do not show up in listings).
- Ability to specify "markdown" as the language; this will pass the paste
  through the markdown parser to generate markup.
- Ability to specify "sections" of code, and thus paste multiple "files" in the
  same paste. Any line starting with "##" signifies a section. The section will
  contain any text following "##" as the title:

    ## test.txt
    This is the first section.

    ## test2.txt
    This is the second section.

  Additionally, if you place a language name in brackets, that language will be
  used for syntax highlighting for that section:

    ## test.js [javascript]
    {
        "text": "highlighted as javascript"
    }

    ## test.php [php]
    echo "This is highlighted as PHP";

  Developers familiar with pastie.org will find the above syntax familiar.
- An API for listing pastes, retrieving individual paste details, and submitting
  pastes. The paste retrieval portion of the API does not require authorization,
  but submitting a paste requires an authorization token. (See the section
  titled "API" below for details.

Installation
------------

Install via composer:

```javascript
{
    "minimum-stability": "dev",
    "repositories": [
        {
            "type": "composer",
            "url": "http://packages.zendframework.com/"
        }
    ],
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

Next, add `EdpMarkdown` to your modules array in `config/application.config.php`.

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

Zend\Db\TableGateway Usage
--------------------------

Currently, only SQLite is supported. To set up your database, do the following
from your application root:

```bash
sqlite data/paste.db < vendor/phly/phly-paste/config/schema.sqlite.sql
```

Make sure the `data/paste.db` file is writeable by your webserver.

Then create the following configuration in your `config/autoload/global.php`
file (or some other autoloadable configuration file in that directory):

```php
return array(
    'db' => array(
        'driver' => 'Pdo',
        'dsn'    => 'sqlite:' . getcwd() . '/data/paste.db',
    ),
    'service_manager' => array(
        'aliases' => array(
            'PhlyPaste\PasteService' => 'PhlyPaste\TableGatewayService',
        ),
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
);
```

Once this is in place, you should be able to create and lists pastes.

CAPTCHA setup
-------------

By default, the "Dumb" CAPTCHA adapter is used. You can setup an alternate one
by providing either global or local configuration under the "phly_paste" key's
"captcha" subkey. Configuration is consistent with `Zend\Captcha\Factory`:

```php
return array(
    'phly_paste' => array(
        'captcha' => array(
            'class' => 'CaptchaClassName',
            'options' => array(/* array of adapter-specific options */),
        ),
    ),
);
```

You can disable CAPTCHA for authenticated users. To do this, you need to define
an alias named `PhlyPaste\AuthService` that points to a service returning a
`Zend\Authentication\AuthenticationService` instance. Once enabled, CAPTCHAs
will no longer be displayed for currently authenticated users.

API
---

An API is also enabled for this module. By default, it goes to the route
described by the path '/paste/api/paste'. The API is JSON only, and expects that
the Accept header matches against the media type 'application/hal+json' (it also
allows 'application/json', but 'application/hal+json' will always be returned). 

The following operations are available:

### GET /paste/api/paste[?page=X]


Retrieves a single page of a list of pastes. The payload looks like the
following:

    HTTP/1.0 200 Ok
    Content-Type: application/json

    {
        "_links": {
            "canonical": {"rel": "canonical", "href": "http://pages.local/paste"},
            "self": {"rel": "self", "href": "http://pages.local/paste/api/paste"},
            "first": {"rel": "first", "href": "http://pages.local/paste/api/paste"},
            "last": {"rel": "last", "href": "http://pages.local/paste/api/paste?page=X"},
            "next": {"rel": "next", "href": "http://pages.local/paste/api/paste?page=2"}
        },
        "items": [
            [
                {"rel": "canonical", "href": "http://pages.local/paste/XYZ"},
                {"rel": "item", "href": "http://pages.local/paste/api/paste/XYZ"}
            ],
            /* ... */
        ]
    }

### GET /paste/api/paste/XYZ12ABC

Fetches information on a single paste. The payload looks like the following:

    HTTP/1.0 200 Ok
    Content-Type: application/json

    {
        "_links": {
            "canonical": {"rel": "canonical", "href": "http://pages.local/paste/XYZ12ABC"},
            "self": {"rel": "self", "href": "http://pages.local/paste/api/paste/XYZ12ABC"},
            "up": {"rel": "up", "href": "http://pages.local/paste/api/paste"}
        },
        "title": "...",
        "language": "...",
        "timestamp": "...",
    }

### POST /paste/api/paste

Expects a JSON body, like the following:

    Accept: application/json
    Content-Type: application/json
    X-PhlyPaste-Token: yourtoken

    {
        "language": "txt",
        "private": "false",
        "content": "This is the paste content..."
    }

You will get the following response payload:

    HTTP/1.0 201 Created
    Location: http://paste.local/paste/XYZ12ABC
    Content-Type: application/json

    {
        "_links": {
            "canonical": {"rel": "canonical", "href": "http://pages.local/paste/XYZ12ABC"},
            "self": {"rel": "self", "href": "http://pages.local/paste/api/paste/XYZ12ABC"}
        }
    }

### Authorization Tokens for Submitting Pastes

As you may have noticed in the previous example, the POST operation requires an
"X-PhlyPaste-Token" header. Tokens are verified against the
`PhlyPaste\TokenService` service, which is simply a
`PhlyPaste\Model\TokenServiceInterface` implementation. By default, a single
implementation is provided, `PhlyPaste\Model\ArrayTokenService`. This
implementation expects that the configuration includes tokens:

```php
return array(
    'phly_paste' => array(
        'tokens' => array(
            'yourtoken',
        ),
    ),
);
```

If you use this approach, make sure that tokens are defined in `.local.php`
files that are stored outside your repository.

Alternately, you may create your own implementation of the
`TokenServiceInterface` that can be used to verify tokens.
