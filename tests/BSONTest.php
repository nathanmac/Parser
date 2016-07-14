<?php

namespace Nathanmac\Utilities\Parser\Tests;

use Nathanmac\Utilities\Parser\Parser;

class BSONTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function parser_validates_bson_data()
    {
        $expected  = ['status' => 123, 'message' => 'hello world'];

        if (function_exists('bson_encode')) {
            $payload = bson_encode($expected);
        } elseif (function_exists('MongoDB\BSON\fromPHP')) {
            $payload = \MongoDB\BSON\fromPHP($expected);
        }

        if (function_exists('bson_decode') || function_exists('MongoDB\BSON\toPHP')) {
            $parser = new Parser();
            $this->assertEquals($expected, $parser->bson($payload));
        }
    }

    /** @test */
    public function parser_empty_bson_data()
    {
        if (function_exists('bson_decode') || function_exists('MongoDB\BSON\toPHP')) {
            $parser = new Parser();
            $this->assertEquals([], $parser->bson(""));
        }
    }

    /** @test */
    public function throw_an_exception_when_bson_library_not_loaded()
    {
        if ( ! (function_exists('bson_decode') || function_exists('MongoDB\BSON\toPHP'))) {
            $this->setExpectedException('Exception', 'Failed To Parse BSON - Supporting Library Not Available');

            $parser = new Parser();
            $this->assertEquals([], $parser->bson(""));
        }
    }

    /** @test */
    public function throws_an_exception_when_parsed_bson_bad_data()
    {
        if (function_exists('bson_decode') || function_exists('MongoDB\BSON\toPHP')) {
            $parser = new Parser();
            $this->setExpectedException('Exception', 'Failed To Parse BSON');
            $parser->bson('as|df>ASFBw924hg2=');
        }
    }

    /** @test */
    public function format_detection_bson()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/bson";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\BSON', $parser->getFormatClass());

        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }
}
