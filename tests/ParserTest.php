<?php

require dirname(__FILE__)."/../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;
use \Mockery as m;

class ParserTest extends PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        m::close();
    }

    /** @test */
    public function wildcards_with_simple_structure_json()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->andReturn('{"email": {"to": "jane.doe@example.com", "from": "john.doe@example.com", "subject": "Hello World", "message": { "body": "Hello this is a sample message" }}}');

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
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->andReturn('{"comments": [{ "title": "hello", "message": "hello world"}, {"title": "world", "message": "world hello"}]}');

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
    public function array_structured_getPayload_json()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn('{"comments": [{ "title": "hello", "message": "hello world"}, {"title": "world", "message": "hello world"}]}');

        $this->assertEquals(array("comments" => array(array("title" => "hello", "message" => "hello world"), array("title" => "world", "message" => "hello world"))), $parser->payload());
    }

    /** @test */
    public function alias_all_check()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn('{"status":123, "message":"hello world"}');

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->all());
    }

    /** @test */
    public function return_value_for_multi_level_key()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->andReturn('{"id": 123, "note": {"headers": {"to": "example@example.com", "from": "example@example.com"}, "body": "Hello World"}}');

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
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->andReturn('{"status":false, "code":123, "note":"", "message":"hello world"}');

        $this->assertEquals('ape', $parser->get('banana', 'ape'));
        $this->assertEquals('123', $parser->get('code', '2345234'));
        $this->assertEquals('abcdef', $parser->get('note', 'abcdef'));
        $this->assertEquals('hello world', $parser->get('message'));
    }

    /** @test */
    public function return_boolean_value_if_getPayload_has_keys()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->times(3)
            ->andReturn('{"status":false, "code":123, "note":"", "message":"hello world"}');

        $this->assertTrue($parser->has('status', 'code'));
        $this->assertFalse($parser->has('banana'));
        $this->assertFalse($parser->has('note'));
    }

    /** @test */
    public function only_return_selected_fields()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->andReturn('{"status":123, "message":"hello world"}');

        $this->assertEquals(array('status' => 123), $parser->only('status'));
    }

    /** @test */
    public function except_do_not_return_selected_fields()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getPayload')
            ->twice()
            ->andReturn('{"status":123, "message":"hello world"}');

        $this->assertEquals(array('status' => 123), $parser->except('message'));
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->except('message.tags'));
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
    public function throw_an_exception_when_parsed_auto_detect_mismatch_content_type()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getFormat')
            ->once()
            ->andReturn('serialize');

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml><status>123</status><message>hello world</message></xml>");

        $this->setExpectedException('Exception', 'Failed To Parse Serialized Data');
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
    }
}
