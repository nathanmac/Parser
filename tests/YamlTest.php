<?php

namespace Nathanmac\Utilities\Parser\Tests;

use \Mockery as m;
use Nathanmac\Utilities\Parser\Parser;

class YamlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tear down after tests
     */
    protected function tearDown()
    {
        m::close();
    }

    /** @test */
    public function parse_auto_detect_serialized_data()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getFormatClass')
            ->once()
            ->andReturn('Nathanmac\Utilities\Parser\Formats\YAML');

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn('---
status: 123
message: "hello world"');

        $this->assertEquals(['status' => 123, 'message' => 'hello world'], $parser->payload());
    }

    /** @test */
    public function parser_validates_yaml_data()
    {
        $parser = new Parser();
        $this->assertEquals(['status' => 123, 'message' => 'hello world'], $parser->yaml('---
status: 123
message: "hello world"'));
    }

    /** @test */
    public function parser_empty_yaml_data()
    {
        $parser = new Parser();
        $this->assertEquals([], $parser->yaml(""));
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
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\YAML', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-yaml";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\YAML', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/yaml";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\YAML', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-yaml";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\YAML', $parser->getFormatClass());

        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }
}
