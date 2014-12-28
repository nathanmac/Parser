<?php

require dirname(__FILE__)."/../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;

class XMLTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function array_structuredgetPayload_xml()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('<xml><comments><title>hello</title><message>hello world</message></comments><comments><title>world</title><message>hello world</message></comments></xml>'));

        $this->assertEquals(array("comments" => array(array("title" => "hello", "message" => "hello world"), array("title" => "world", "message" => "hello world"))), $parser->payload('application/xml'));
    }

    /** @test */
    public function parse_auto_detect_xml_data()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload', 'getFormat'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));

        $parser->expects($this->any())
            ->method('getFormat')
            ->will($this->returnValue('xml'));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
    }
    /** @test */
    public function parse_auto_detect_xml_data_define_content_type_as_param()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload('application/xml'));
    }

    /** @test */
    public function throw_an_exception_when_parsed_auto_detect_mismatch_content_type()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload', 'getFormat'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));

        $parser->expects($this->any())
            ->method('getFormat')
            ->will($this->returnValue('serialize'));

        $this->setExpectedException('Exception', 'Failed To Parse Serialized Data');
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
    }

    /** @test */
    public function parser_validates_xml_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->xml("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));
    }

    /** @test */
    public function parser_empty_xml_data()
    {
        $parser = new Parser();
        $this->assertEquals(array(), $parser->xml(""));
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
        $this->assertEquals('xml', $parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/xml";
        $this->assertEquals('xml', $parser->getFormat());
    }
}

