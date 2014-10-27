<?php

require dirname(__FILE__)."/../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;

class ParserPHPTest extends PHPUnit_Framework_TestCase
{

    /** @test */
    public function wildcards_with_simple_structure_json()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"email": {"to": "jane.doe@example.com", "from": "john.doe@example.com", "subject": "Hello World", "message": { "body": "Hello this is a sample message" }}}'));

        $this->assertTrue($parser->has('email.to'));
        $this->assertTrue($parser->has('email.message.*'));
        $this->assertTrue($parser->has('email.message.%'));
        $this->assertTrue($parser->has('email.message.:first'));
        $this->assertTrue($parser->has('email.message.:last'));
        $this->assertFalse($parser->has('message.email.*'));
        $this->assertFalse($parser->has('message.email.%'));
        $this->assertFalse($parser->has('message.email.:first'));
        $this->assertFalse($parser->has('message.email.:last'));
        $this->assertEquals("Hello this is a sample message", $parser->get('email.message.%'));
        $this->assertEquals("Hello this is a sample message", $parser->get('email.message.:first'));
        $this->assertEquals("jane.doe@example.com", $parser->get('email.*'));
        $this->assertEquals("jane.doe@example.com", $parser->get('email.:first'));
        $this->assertEquals(array('body' => 'Hello this is a sample message'), $parser->get('email.:last'));
        $this->assertEquals("jane.doe@example.com", $parser->get('email.:index[0]'));
        $this->assertEquals("john.doe@example.com", $parser->get('email.:index[1]'));
    }

    /** @test */
    public function wildcards_with_array_structure_json()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"comments": [{ "title": "hello", "message": "hello world"}, {"title": "world", "message": "world hello"}]}'));

        $this->assertTrue($parser->has('comments.*.title'));
        $this->assertTrue($parser->has('comments.%.title'));
        $this->assertTrue($parser->has('comments.:index[1].title'));
        $this->assertTrue($parser->has('comments.:first.title'));
        $this->assertTrue($parser->has('comments.:last.title'));
        $this->assertEquals('hello', $parser->get('comments.:index[0].title'));
        $this->assertEquals('world', $parser->get('comments.:index[1].title'));
        $this->assertEquals('world', $parser->get('comments.:last.title'));
        $this->assertEquals('hello', $parser->get('comments.*.title'));
        $this->assertFalse($parser->has('comments.:index[99]'));
        $this->assertFalse($parser->has('comments.:index[99].title'));
        $this->assertEquals(array('title' => 'hello', 'message' => 'hello world'), $parser->get('comments.*'));
        $this->assertEquals(array('title' => 'hello', 'message' => 'hello world'), $parser->get('comments.%'));
        $this->assertEquals(array('title' => 'hello', 'message' => 'hello world'), $parser->get('comments.:first'));
        $this->assertEquals(array('title' => 'world', 'message' => 'world hello'), $parser->get('comments.:last'));
        $this->assertEquals(array('title' => 'hello', 'message' => 'hello world'), $parser->get('comments.:index[0]'));
        $this->assertEquals(array('title' => 'world', 'message' => 'world hello'), $parser->get('comments.:index[1]'));
    }

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
    public function array_structuredgetPayload_json()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
                    ->method('getPayload')
                    ->will($this->returnValue('{"comments": [{ "title": "hello", "message": "hello world"}, {"title": "world", "message": "hello world"}]}'));

        $this->assertEquals(array("comments" => array(array("title" => "hello", "message" => "hello world"), array("title" => "world", "message" => "hello world"))), $parser->payload());
    }

    /** @test */
    public function alias_all_check()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"status":123, "message":"hello world"}'));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->all());
    }

    /** @test */
    public function return_value_for_multi_level_key()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"id": 123, "note": {"headers": {"to": "example@example.com", "from": "example@example.com"}, "body": "Hello World"}}'));

        $this->assertEquals('123', $parser->get('id'));
        $this->assertEquals('Hello World', $parser->get('note.body'));
        $this->assertEquals('example@example.com', $parser->get('note.headers.to'));
        $this->assertTrue($parser->has('note.headers.to'));

        $this->assertEquals(array('id' => 123, 'note' => array('headers' => array('from' => 'example@example.com'), 'body' => 'Hello World')), $parser->except('note.headers.to'));
        $this->assertEquals(array('id' => 123, 'note' => array('headers' => array('to' => 'example@example.com', 'from' => 'example@example.com'))), $parser->except('note.body'));

        $this->assertEquals(array('note' => array('headers' => array('to' => 'example@example.com', 'from' => 'example@example.com'))), $parser->only('note.headers.to', 'note.headers.from'));
        $this->assertEquals(array('id' => 123, 'status' => null, 'note' => array('body' => 'Hello World')), $parser->only('note.body', 'id', 'status'));
    }

    /** @test */
    public function return_value_for_selected_key_use_default_if_not_found()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"status":false, "code":123, "note":"", "message":"hello world"}'));

        $this->assertEquals('ape', $parser->get('banana', 'ape'));
        $this->assertEquals('123', $parser->get('code', '2345234'));
        $this->assertEquals('abcdef', $parser->get('note', 'abcdef'));
        $this->assertEquals('hello world', $parser->get('message'));
    }

    /** @test */
    public function return_boolean_value_ifgetPayload_has_keys()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"status":false, "code":123, "note":"", "message":"hello world"}'));

        $this->assertTrue($parser->has('status', 'code'));
        $this->assertFalse($parser->has('banana'));
        $this->assertFalse($parser->has('note'));
    }

    /** @test */
    public function only_return_selected_fields()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"status":123, "message":"hello world"}'));

        $this->assertEquals(array('status' => 123), $parser->only('status'));
    }

    /** @test */
    public function except_do_not_return_selected_fields()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"status":123, "message":"hello world"}'));

        $this->assertEquals(array('status' => 123), $parser->except('message'));
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->except('message.tags'));
    }

    /** @test */
    public function parse_auto_detect_json_data()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('{"status":123, "message":"hello world"}'));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
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
    public function parser_validates_json_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->json('{"status":123, "message":"hello world"}'));
    }

    /** @test */
    public function parser_empty_json_data()
    {
        $parser = new Parser();
        $this->assertEquals(array(), $parser->json(""));
    }

    /** @test */
    public function parser_validates_serialize_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->serialize('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}'));
    }

    /** @test */
    public function parser_empty_serialize_data()
    {
        $parser = new Parser();
        $this->assertEquals(array(), $parser->serialize(""));
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
    public function parser_validates_yaml_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->yaml('---
status: 123
message: "hello world"'));
    }

    /** @test */
    public function parser_empty_yaml_data()
    {
        $parser = new Parser();
        $this->assertEquals(array(), $parser->yaml(""));
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
        $this->assertEquals('json', $parser->getFormat());

        $_SERVER['CONTENT_TYPE'] = "somerandomstuff";
        $this->assertEquals('json', $parser->getFormat());
    }

    /** @test */
    public function format_detection_json()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/json";
        $this->assertEquals('json', $parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-javascript";
        $this->assertEquals('json', $parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/javascript";
        $this->assertEquals('json', $parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-javascript";
        $this->assertEquals('json', $parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-json";
        $this->assertEquals('json', $parser->getFormat());
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

    /** @test */
    public function format_detection_yaml()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/yaml";
        $this->assertEquals('yaml', $parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-yaml";
        $this->assertEquals('yaml', $parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/yaml";
        $this->assertEquals('yaml', $parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-yaml";
        $this->assertEquals('yaml', $parser->getFormat());
    }

    /** @test */
    public function format_detection_serialize()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/vnd.php.serialized";
        $this->assertEquals('serialize', $parser->getFormat());
    }

    /** @test */
    public function format_detection_query_string()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-www-form-urlencoded";
        $this->assertEquals('querystr', $parser->getFormat());
    }
}
