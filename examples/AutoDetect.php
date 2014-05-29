<?php
require "../vendor/autoload.php";
require "../src/Parser.php";

use NathanMac\Parser;

$parser = new Parser();

echo "<h1>POST/PUT Payload AutoDetect - Example</h1>";

$parsed = $parser->payload('application/xml');

echo "<pre>";
print_r($parsed);
echo "</pre>";
