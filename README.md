Parser
======

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nathanmac/Parser.svg?style=flat-square)](https://packagist.org/packages/nathanmac/parser)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/nathanmac/Parser/master.svg?style=flat-square)](https://travis-ci.org/nathanmac/Parser)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/nathanmac/Parser.svg?style=flat-square)](https://scrutinizer-ci.com/g/nathanmac/Parser/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/nathanmac/Parser.svg?style=flat-square)](https://scrutinizer-ci.com/g/nathanmac/Parser)
[![Total Downloads](https://img.shields.io/packagist/dt/nathanmac/Parser.svg?style=flat-square)](https://packagist.org/packages/nathanmac/Parser)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c5bc4a3d-b954-4901-905f-cd49fb8c3986/mini.png)](https://insight.sensiolabs.com/projects/c5bc4a3d-b954-4901-905f-cd49fb8c3986)

Simple PHP Parser Library for API Development, parse a post http payload into a php array.

Installation
------------

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `Nathanmac/Parser`.

	"require": {
		"Nathanmac/Parser": "3.*"
	}

Next, update Composer from the Terminal:

    composer update


### Laravel/Lumen Users

Laravel/Lumen Verison | Supported Library Verison
----------------------|--------------------------
Laravel/Lumen 5+ | > 3.*
Laravel 4  | 2.*

#### Laravel Users (Adding the Service Provider)

If you are a Laravel user, then there is a service provider that you can make use of to automatically prepare the bindings and such.

Include the service provider within `app/config/app.php`.

```php
'providers' => [
    '...',
    'Nathanmac\Utilities\Parser\ParserServiceProvider'
];
```

And, for convenience, add a facade alias to this same file at the bottom:

```php
'aliases' => [
    '...',
    'Parser' => 'Nathanmac\Utilities\Parser\Facades\Parser',
];
```

#### Lumen Users (Adding the Service Provider)

If you are a Lumen user, then there is a service provider that you can make use of to automatically prepare the binding and such.

```php
// bootstrap/app.php

$app->register('Nathanmac\Utilities\Parser\ParserServiceProvider');
```

Lumen users can also add the facade alias.

```php
// bootstrap/app.php

class_alias('Nathanmac\Utilities\Parser\Facades\Parser', 'Parser');
```

#### Using the Facade

```php
public function index()
{
    Parser::payload('application/json');

    Parser::json($payload);		    // JSON > Array
    Parser::xml($payload);		    // XML > Array
    Parser::yaml($payload);		    // YAML > Array
    Parser::querystr($payload);	    // Query String > Array
    Parser::serialize($payload);	// Serialized Object > Array
	Parser::bson($payload);	        // BSON > Array
	Parser::msgpack($payload);	    // MSGPack > Array

    Parser::all();                         // Return all values
    Parser::get('key', 'default value');   // Get value by key, set an optional default.
    Parser::has('key');                    // Does a key exist, with value.
    Parser::only('id', 'name', 'email');   // Only return value from the selected keys.
    Parser::except('password');            // Don't return values from the selected keys.
}
```

Usage
-----

#### Parsing Functions
```php
$parser->json($payload);		// JSON > Array
$parser->xml($payload);		    // XML > Array
$parser->yaml($payload);		// YAML > Array
$parser->querystr($payload);	// Query String > Array
$parser->serialize($payload);	// Serialized Object > Array
$parser->bson($payload);     	// BSON > Array
$parser->msgpack($payload);   	// MSGPack > Array
```

#### Parse Input/Payload (PUT/POST)
```php
$parser = new Parser();
$parser->payload();		                // Auto Detect Type - 'Content Type' HTTP Header
$parser->payload('application/json');	// Specifiy the content type
```

#### Helper functions
```php
$parser = new Parser();
$parser->all();                         // Return all values
$parser->get('key', 'default value');   // Get value by key, set an optional default.
$parser->has('key');                    // Does a key exist, with value.
$parser->only('id', 'name', 'email');   // Only return value from the selected keys.
$parser->except('password');            // Don't return values from the selected keys.
```

#### Mask function
The mask function processes payload data using a configuration mask, thereby returning only a selected subset of the data.
It works just like the `only` method but with the added benefit of allowing you to specify a mask in the form of an array,
this means you can generate masks on-the-fly based on system and/or user defined conditions.

##### Demo
###### Mask
Defining the mask, masks consist of basic array structure, for this particular example we have some rules for the data
to be returned they include:
    - the title of the post
    - all the body's for all the comments.

```php
$mask = [
    'post' => [
        'title' => '*',
        'comments' => [
            'body' => '*'
        ]
    ]
];
```

###### Sample Payload
```json
{
    "post": {
        "title": "Hello World",
        "author": "John Smith",
        "comments": [
            {"body": "This is a comment", "date": "2015-02-20"},
            {"body": "This is another comment", "date": "2015-05-09"}
        ]
    }
}
```

###### Applying the Mask
```php
    $parser = new Parser();
    $output = $parser->mask($mask);
```

###### Output
This is the output generated as a result of applying the mask against the sample payload provided above.

```php
$output = [
    'post' => [
        'title' => 'Hello World',
        'comments' => [
            ['body' => 'This is a comment'],
            ['body' => 'This is another comment']
        ]
    ]
];
```

#### Wildcards/Special Keys (*, %, :first, :last, :index[0], :item[0])
```php
$parser = new Parser();
$parser->get('message.*');          // Get value by key. (Wildcard key returns first item found)
$parser->has('message.*');          // Does a key exist, with value. (Wildcard key returns first item found)
$parser->get('message.:first');     // Get value by key. (:first key returns first item found)
$parser->has('message.:first');     // Does a key exist, with value. (:first key returns first item found)
$parser->get('message.:last');      // Get value by key. (:last key returns first item found)
$parser->has('message.:last');      // Does a key exist, with value. (:last key returns first item found)
$parser->get('message.:index[0]');  // Get value by key. (:index[0] key returns item at index 0)
$parser->has('message.:index[0]');  // Does a key exist, with value. (:index[0] key returns item at index 0)
$parser->get('message.:item[0]');   // Get value by key. (:item[0] key returns item at index 0)
$parser->has('message.:item[0]');   // Does a key exist, with value. (:item[0] key returns item at index 0)
```

#### Parse JSON
```php
$parser = new Parser();
$parsed = $parser->json('
	{
		"message": {
			"to": "Jack Smith",
			"from": "Jane Doe",
			"subject": "Hello World",
			"body": "Hello, whats going on..."
		}
	}');
```

#### Parse XML
```php
$parser = new Parser();
$parsed = $parser->xml('
			<?xml version="1.0" encoding="UTF-8"?>
			<xml>
				<message>
					<to>Jack Smith</to>
					<from>Jane Doe</from>
					<subject>Hello World</subject>
					<body>Hello, whats going on...</body>
				</message>
			</xml>');
```

#### Parse Query String
```php
$parser = new Parser();
$parsed = $parser->querystr('to=Jack Smith&from=Jane Doe&subject=Hello World&body=Hello, whats going on...');
```

#### Parse Serialized Object
```php
$parser = new Parser();
$parsed = $parser->serialize('a:1:{s:7:"message";a:4:{s:2:"to";s:10:"Jack Smith";s:4:"from";s:8:"Jane Doe";s:7:"subject";s:11:"Hello World";s:4:"body";s:24:"Hello, whats going on...";}}');
```

#### Parse YAML
```php
$parser = new Parser();
$parsed = $parser->yaml('
				---
				message:
				    to: "Jack Smith"
				    from: "Jane Doe"
				    subject: "Hello World"
				    body: "Hello, whats going on..."
				');
```

#### Parse BSON
```php
$parser = new Parser();
$parsed = $parser->bson('BSON DATA HERE');
```

#### Parse MSGPack
```php
$parser = new Parser();
$parsed = $parser->msgpack('MSGPACK DATA HERE');
```

Custom Parsers/Formatters
-------------------------

You can make your own custom parsers/formatters by implementing [FormatInterface](https://github.com/nathanmac/Parser/blob/master/src/Formats/FormatInterface.php), the below example demostrates the use of a custom parser/formatter.

```php
use Nathanmac\Utilities\Parser\Formats\FormatInterface;

/**
 * Custom Formatter
 */

class CustomFormatter implements FormatInterface {
    /**
     * Parse Payload Data
     *
     * @param string $payload
     *
     * @return array
     *
     * @throws ParserException
     */
    public function parse($payload)
    {
        $payload; // Raw payload data

        $output = // Process raw payload data to array

        return $output; // return array parsed data
    }
}
```

##### Using the CustomFormatter

```php
use Acme\Formatters\CustomFormatter;

$parser = new Parser();
$parsed = $parser->parse('RAW PAYLOAD DATA', new CustomFormatter());
```

Testing
-------

To test the library itself, run the tests:

    composer test

Contributing
------------

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Credits
-------

- [nathanmac](https://github.com/nathanmac)
- [All Contributors](../../contributors)

License
-------

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

Appendix
--------

###### Supported Content-Types
```
XML
---
application/xml > XML
text/xml > XML

JSON
----
application/json > JSON
application/x-javascript > JSON
text/javascript > JSON
text/x-javascript > JSON
text/x-json > JSON

YAML
----
text/yaml > YAML
text/x-yaml > YAML
application/yaml > YAML
application/x-yaml > YAML

BSON
----
application/bson > BSON

MSGPack
-------
application/msgpack > MSGPack
application/x-msgpack > MSGPack

MISC
----
application/vnd.php.serialized > Serialized Object
application/x-www-form-urlencoded' > Query String
```
