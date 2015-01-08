Parser
======

[![License](http://img.shields.io/packagist/l/nathanmac/parser.svg)](https://github.com/nathanmac/Parser/blob/master/LICENSE.md)
[![Build Status](https://travis-ci.org/nathanmac/Parser.svg?branch=master)](https://travis-ci.org/nathanmac/Parser)
[![Coverage Status](https://coveralls.io/repos/nathanmac/Parser/badge.png?branch=master)](https://coveralls.io/r/nathanmac/Parser?branch=master)
[![Code Climate](https://codeclimate.com/github/nathanmac/Parser.png)](https://codeclimate.com/github/nathanmac/Parser)
[![Latest Stable Version](https://poser.pugx.org/nathanmac/parser/v/stable.svg)](https://packagist.org/packages/nathanmac/parser)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c5bc4a3d-b954-4901-905f-cd49fb8c3986/mini.png)](https://insight.sensiolabs.com/projects/c5bc4a3d-b954-4901-905f-cd49fb8c3986)

Simple PHP Parser Library for API Development, parse a post http payload into a php array.

Installation
------------

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `Nathanmac/Parser`.

	"require": {
		"Nathanmac/Parser": "2.*"
	}

Next, update Composer from the Terminal:

    composer update


### Laravel Users

If you are a Laravel user, then there is a service provider that you can make use of to automatically prepare the bindings and such.

```php

// app/config/app.php

'providers' => [
    '...',
    'Nathanmac\Utilities\Parser\ParserServiceProvider'
];
```

When this provider is booted, you'll have access to a helpful `Parser` facade, which you may use in your controllers.

```php
public function index()
{
    Parser::payload('application/json');
    
    Parser::json($payload);		    // JSON > Array
    Parser::xml($payload);		    // XML > Array
    Parser::yaml($payload);		    // YAML > Array
    Parser::querystr($payload);	    // Query String > Array
    Parser::serialize($payload);	// Serialized Object > Array
    
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

To test the library itself, run the PHPUnit tests:

    phpunit tests/


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

MISC
----
application/vnd.php.serialized > Serialized Object
application/x-www-form-urlencoded' > Query String
```
