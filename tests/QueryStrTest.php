<?php

require dirname(__FILE__)."/../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;

class QueryStrTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function parse_auto_detect_query_string_data()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload', 'getFormat'));

        $parser->expects($this->any())
            ->method('getFormat')
            ->will($this->returnValue('querystr'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('status=123&message=hello world'));

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
