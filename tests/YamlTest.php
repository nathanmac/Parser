<?php

require dirname(__FILE__)."/../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;

class YamlTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function parse_auto_detect_serialized_data()
    {
        $parser = $this->getMock('Nathanmac\Utilities\Parser\Parser', array('getPayload', 'getFormat'));

        $parser->expects($this->any())
            ->method('getFormat')
            ->will($this->returnValue('yaml'));

        $parser->expects($this->any())
            ->method('getPayload')
            ->will($this->returnValue('---
status: 123
message: "hello world"'));

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
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

    /** @test */
    public function throws_an_exception_when_parsed_yaml_bad_data()
    {
        $parser = new Parser();
        $this->setExpectedException('Exception', 'Failed To Parse YAML');
        $parser->yaml('as|df>ASFBw924hg2=
                        sfgsaf:asdfasf');
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
}
