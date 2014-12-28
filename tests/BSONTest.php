<?php

require dirname(__FILE__) . "/../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;
use \Mockery as m;

class BSONTest extends PHPUnit_Framework_TestCase {

    protected function tearDown()
    {
        m::close();
    }

    /** @test */
    public function parser_validates_bson_data()
    {
        if (function_exists('bson_decode')) {
            $expected = array('status' => 123, 'message' => 'hello world');
            $payload = bson_encode($expected);

            $parser = new Parser();
            $this->assertEquals($expected, $parser->bson($payload));
        }
    }

    /** @test */
    public function parser_empty_bson_data()
    {
        if (function_exists('bson_decode')) {
            $parser = new Parser();
            $this->assertEquals(array(), $parser->bson(""));
        }
    }

    /** @test */
    public function throw_an_exception_when_bson_library_not_loaded()
    {
        if (! function_exists('bson_decode')) {
            $this->setExpectedException('Exception', 'Failed To Parse BSON - Supporting Library Not Available');

            $parser = new Parser();
            $this->assertEquals(array(), $parser->bson(""));
        }
    }

    /** @test */
    public function throws_an_exception_when_parsed_bson_bad_data()
    {
        if (! function_exists('bson_decode')) {
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
        $this->assertEquals('bson', $parser->getFormat());
    }
}
