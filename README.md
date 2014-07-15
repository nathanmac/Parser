Parser
======

[![Build Status](https://travis-ci.org/nathanmac/Parser.svg?branch=master)](https://travis-ci.org/nathanmac/Parser)

Simple PHP Parser Library for API Development, parse a post http payload into a php array.

Installation
------------

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `Nathanmac/Parser`.

	"require": {
		"Nathanmac/Parser": "1.*"
	}

Next, update Composer from the Terminal:

    composer update

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
    $parser->all();                     // Return all values
    $parser->get('key', 'default');     // Get value by key, set an optional default.
    $parser->only('id', 'name');        // Only return value from the selected keys.
    $parser->except('password');        // Don't return values from the selected keys.
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

MISC
----
application/vnd.php.serialized > Serialized Object
application/x-www-form-urlencoded' > Query String
```
