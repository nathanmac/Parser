<?php

require "../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;
$parser = new Parser();

echo "<h1>XML Example</h1>";
$parsed = $parser->xml("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                        <xml>
                            <message>
                                <to>Jack Smith</to>
                                <from>Jane Doe</from>
                                <subject>Hello World</subject>
                                <body>Hello, whats going on...</body>
                            </message>
                        </xml>");

echo "<pre>";
print_r($parsed);
echo "</pre>";