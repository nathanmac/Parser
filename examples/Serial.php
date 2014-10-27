<?php

require "../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;

$parser = new Parser();

echo "<h1>Serialised Object Example</h1>";
$parsed = $parser->serialize('a:1:{s:7:"message";a:4:{s:2:"to";s:10:"Jack Smith";s:4:"from";s:8:"Jane Doe";s:7:"subject";s:11:"Hello World";s:4:"body";s:24:"Hello, whats going on...";}}');

echo "<pre>";
print_r($parsed);
echo "</pre>";