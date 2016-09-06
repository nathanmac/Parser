<?php

namespace Nathanmac\Utilities\Parser\Tests;

use \Mockery as m;
use Nathanmac\Utilities\Parser\Parser;

class XMLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tear down after tests
     */
    protected function tearDown()
    {
        m::close();
    }

    /** @test */
    public function null_values_for_empty_values()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn('<xml><comments><title></title><message>hello world</message></comments><comments><title>world</title><message></message></comments></xml>');

        $this->assertEquals(["comments" => [["title" => null, "message" => "hello world"], ["title" => "world", "message" => null]]], $parser->payload('application/xml'));
    }

    /** @test */
    public function array_structured_getPayload_xml()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn('<xml><comments><title>hello</title><message>hello world</message></comments><comments><title>world</title><message>hello world</message></comments></xml>');

        $this->assertEquals(["comments" => [["title" => "hello", "message" => "hello world"], ["title" => "world", "message" => "hello world"]]], $parser->payload('application/xml'));
    }

    /** @test */
    public function parse_auto_detect_xml_data()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getFormatClass')
            ->once()
            ->andReturn('Nathanmac\Utilities\Parser\Formats\XML');

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>");

        $this->assertEquals(['status' => 123, 'message' => 'hello world'], $parser->payload());
    }

    /** @test */
    public function parse_auto_detect_xml_data_define_content_type_as_param()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>");

        $this->assertEquals(['status' => 123, 'message' => 'hello world'], $parser->payload('application/xml'));
    }

    /** @test */
    public function parser_validates_xml_data()
    {
        $parser = new Parser();
        $this->assertEquals(['status' => 123, 'message' => 'hello world'], $parser->xml("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));
    }

    /** @test */
    public function parser_validates_xml_data_with_attribute()
    {
        $parser = new Parser();
        $this->assertEquals(['status' => 123, 'message' => 'hello world', '@name' => 'root'], $parser->xml("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml name=\"root\"><status>123</status><message>hello world</message></xml>"));
    }

    /** @test */
    public function parser_validates_xml_data_with_namespace()
    {
        $parser = new Parser();
        $this->assertEquals(['status' => 123, 'ns:message' => 'hello world'], $parser->xml("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml xmlns:ns=\"data:namespace\"><status>123</status><ns:message>hello world</ns:message></xml>"));
    }

    /** @test */
    public function parser_validates_xml_data_with_attribute_and_namespace()
    {
        $parser = new Parser();
        $this->assertEquals(['status' => 123, 'ns:message' => 'hello world', '@name' => 'root'], $parser->xml("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml name=\"root\" xmlns:ns=\"data:namespace\"><status>123</status><ns:message>hello world</ns:message></xml>"));
    }

    /** @test */
    public function parser_empty_xml_data()
    {
        $parser = new Parser();
        $this->assertEquals([], $parser->xml(""));
    }

    /** @test */
    public function throws_an_exception_when_parsed_xml_bad_data()
    {
        $parser = new Parser();
        $this->setExpectedException('Exception', 'Failed To Parse XML');
        $parser->xml('as|df>ASFBw924hg2=');
    }

    /** @test */
    public function format_detection_xml()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/xml";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\XML', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/xml; charset=utf8";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\XML', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "charset=utf8; application/xml";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\XML', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "APPLICATION/XML";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\XML', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/xml";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\XML', $parser->getFormatClass());

        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }

    /** @test */
    public function parser_validates_xml_with_spaces_and_new_lines()
    {
        $parser = new Parser();
        $this->assertEquals(['status' => 123, 'message' => 'hello world', '@name' => 'root'], $parser->xml("<?xml version=\"1.0\" encoding=\"UTF-8\"?> <xml name=\"root\"> \n <status>123</status> <message>hello world</message></xml>"));
    }
}
