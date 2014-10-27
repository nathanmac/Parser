<?php
require "../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;

$parser = new Parser();

echo "<h1>POST/PUT Payload AutoDetect - Example</h1>";

$parsed = $parser->payload('application/xml');

echo "<pre>";
print_r($parsed);
echo "</pre>";
