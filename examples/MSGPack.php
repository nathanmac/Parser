<?php

require "../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;

$parser = new Parser();

echo "<h1>JSON Example</h1>";
$parsed = $parser->json('
    {
        "message": {
            "to": "Jack Smith",
            "from": "Jane Doe",
            "subject": "Hello World",
            "body": "Hello, whats going on..."
        }
    }');

echo "<pre>";
print_r($parsed);
echo "</pre>";