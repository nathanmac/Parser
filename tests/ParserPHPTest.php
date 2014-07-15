<?php

require dirname(__FILE__)."/../vendor/autoload.php";

use Nathanmac\ParserUtility\Parser;

class ParserPHPTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function return_value_for_selected_key_use_default_if_not_found()
    {
        $parser = $this->getMock('Nathanmac\ParserUtility\Parser', array('_payload'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue('{"status":false, "code":123, "note":"", "message":"hello world"}'));

        $this->assertEquals('ape', $parser->get('banana', 'ape'));
        $this->assertEquals('123', $parser->get('code', '2345234'));
        $this->assertEquals('abcdef', $parser->get('note', 'abcdef'));
    }

    /** @test */
    public function return_boolean_value_if_payload_has_keys()
    {
        $parser = $this->getMock('Nathanmac\ParserUtility\Parser', array('_payload'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue('{"status":false, "code":123, "note":"", "message":"hello world"}'));

        $this->assertTrue($parser->has('status', 'code'));
        $this->assertFalse($parser->has('banana', 'status'));
        $this->assertFalse($parser->has('banana'));
        $this->assertFalse($parser->has('note'));
    }

    /** @test */
    public function only_return_selected_fields()
    {
        $parser = $this->getMock('Nathanmac\ParserUtility\Parser', array('_payload'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue('{"status":123, "message":"hello world"}'));

        $this->assertEquals(array('status' => 123), $parser->only('status'));
    }

    /** @test */
    public function except_do_not_return_selected_fields()
    {
        $parser = $this->getMock('Nathanmac\ParserUtility\Parser', array('_payload'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue('{"status":123, "message":"hello world"}'));

        $this->assertEquals(array('status' => 123), $parser->except('message'));
    }

    /** @test */
    public function parse_auto_detect_json_data()
    {
        $parser = $this->getMock('Nathanmac\ParserUtility\Parser', array('_payload'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue('{"status":123, "message":"hello world"}'));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
    }

    /** @test */
    public function parse_auto_detect_xml_data()
    {
        $parser = $this->getMock('Nathanmac\ParserUtility\Parser', array('_payload', '_format'));

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
        $parser = $this->getMock('Nathanmac\ParserUtility\Parser', array('_payload'));

        $parser->expects($this->any())
            ->method('_payload')
            ->will($this->returnValue("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>"));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload('application/xml'));
    }

    /** @test */
    public function throw_an_exception_when_parsed_auto_detect_mismatch_content_type()
    {
        $parser = $this->getMock('Nathanmac\ParserUtility\Parser', array('_payload', '_format'));

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

    /** @test */
    public function format_detection_defaults_to_json()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "somerandomstuff";
        $this->assertEquals('json', $parser->_format());
    }

    /** @test */
    public function format_detection_json()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/json";
        $this->assertEquals('json', $parser->_format());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-javascript";
        $this->assertEquals('json', $parser->_format());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/javascript";
        $this->assertEquals('json', $parser->_format());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-javascript";
        $this->assertEquals('json', $parser->_format());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-json";
        $this->assertEquals('json', $parser->_format());
    }

    /** @test */
    public function format_detection_xml()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/xml";
        $this->assertEquals('xml', $parser->_format());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/xml";
        $this->assertEquals('xml', $parser->_format());
    }

    /** @test */
    public function format_detection_yaml()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/yaml";
        $this->assertEquals('yaml', $parser->_format());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-yaml";
        $this->assertEquals('yaml', $parser->_format());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/yaml";
        $this->assertEquals('yaml', $parser->_format());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-yaml";
        $this->assertEquals('yaml', $parser->_format());
    }

    /** @test */
    public function format_detection_serialize()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/vnd.php.serialized";
        $this->assertEquals('serialize', $parser->_format());
    }

    /** @test */
    public function format_detection_query_string()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-www-form-urlencoded";
        $this->assertEquals('querystr', $parser->_format());
    }
}