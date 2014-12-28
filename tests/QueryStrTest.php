<?php

require dirname(__FILE__)."/../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;
use \Mockery as m;

class QueryStrTest extends PHPUnit_Framework_TestCase {

    protected function tearDown()
    {
        m::close();
    }

    /** @test */
    public function parse_auto_detect_query_string_data()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getFormat')
            ->once()
            ->andReturn('querystr');

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn('status=123&message=hello world');

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
    }

    /** @test */
    public function parser_validates_query_string_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->querystr('status=123&message=hello world'));
    }

    /** @test */
    public function parser_empty_query_string_data()
    {
        $parser = new Parser();
        $this->assertEquals(array(), $parser->querystr(""));
    }

    /** @test */
    public function format_detection_query_string()
    {
        $parser = new Parser();
        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-www-form-urlencoded";
        $this->assertEquals('querystr', $parser->getFormat());
    }
}
