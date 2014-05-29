<?php

require dirname(__FILE__)."/../vendor/autoload.php";
require dirname(__FILE__).'/../src/Parser.php';

use NathanMac\Parser;

class ParserPHPTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function parse_auto_detect_json_data()
    {
        $parser = $this->getMock('NathanMac\Parser', array('_payload'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue('{"status":123, "message":"hello world"}'));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
    }

    /** @test */
    public function parse_auto_detect_xml_data()
    {
        $parser = $this->getMock('NathanMac\Parser', array('_payload', '_format'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));

        $parser->expects($this->any())
            ->method('_format')
            ->will($this->returnValue('xml'));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
    }
    /** @test */
    public function parse_auto_detect_xml_data_define_content_type_as_param()
    {
        $parser = $this->getMock('NathanMac\Parser', array('_payload'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload('application/xml'));
    }

    /** @test */
    public function throw_an_exception_when_parsed_auto_detect_mismatch_content_type()
    {
        $parser = $this->getMock('NathanMac\Parser', array('_payload', '_format'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));

        $parser->expects($this->any())
            ->method('_format')
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
    public function parser_validates_json_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->json('{"status":123, "message":"hello world"}'));
    }

    /** @test */
    public function parser_validates_serialize_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->serialize('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}'));
    }

    /** @test */
    public function parser_validates_query_string_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->querystr('status=123&message=hello world'));
    }

    /** @test */
    public function parser_validates_yaml_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->yaml('---
status: 123
message: "hello world"'));
    }

    /**
     * @test
     */
    public function throws_an_exception_when_parsed_xml_bad_data()
    {
        $parser = new Parser();
        $this->setExpectedException('Exception', 'Failed To Parse XML');
        $parser->xml('as|df>ASFBw924hg2=');
    }

    /**
     * @test
     */
    public function throws_an_exception_when_parsed_json_bad_data()
    {
        $parser = new Parser();
        $this->setExpectedException('Exception', 'Failed To Parse JSON');
        $parser->json('as|df>ASFBw924hg2=');
    }

    /**
     * @test
     */
    public function throws_an_exception_when_parsed_serialize_bad_data()
    {
        $parser = new Parser();
        $this->setExpectedException('Exception', 'Failed To Parse Serialized Data');
        $parser->serialize('as|df>ASFBw924hg2=');
    }

    /**
     * @test
     */
    public function throws_an_exception_when_parsed_yaml_bad_data()
    {
        $parser = new Parser();
        $this->setExpectedException('Exception', 'Failed To Parse YAML');
        $parser->yaml('as|df>ASFBw924hg2=
                        sfgsaf:asdfasf');
    }


}