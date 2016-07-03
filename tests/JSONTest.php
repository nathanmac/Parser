<?php

namespace Nathanmac\Utilities\Parser\Tests;

use \Mockery as m;
use Nathanmac\Utilities\Parser\Parser;

class JSONTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tear down after tests
     */
    protected function tearDown()
    {
        m::close();
    }

    /** @test */
    public function parse_auto_detect_json_data()
    {
        $parser = m::mock('Nathanmac\Utilities\Parser\Parser')
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $parser->shouldReceive('getFormatClass')
            ->twice()
            ->andReturn('Nathanmac\Utilities\Parser\Formats\JSON');

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn('{"status":123, "message":"hello world"}');

        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\JSON', $parser->getFormatClass());
        $this->assertEquals(['status' => 123, 'message' => 'hello world'], $parser->payload());
    }

    /** @test */
    public function parser_validates_json_data()
    {
        $parser = new Parser();
        $this->assertEquals(['status' => 123, 'message' => 'hello world'], $parser->json('{"status":123, "message":"hello world"}'));
    }

    /** @test */
    public function parser_empty_json_data()
    {
        $parser = new Parser();
        $this->assertEquals([], $parser->json(""));
    }

    /** @test */
    public function throws_an_exception_when_parsed_json_bad_data()
    {
        $parser = new Parser();
        $this->setExpectedException('Exception', 'Failed To Parse JSON');
        $parser->json('as|df>ASFBw924hg2=');
    }

    /** @test */
    public function format_detection_json()
    {
        $parser = new Parser();

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/json";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\JSON', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-javascript";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\JSON', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/javascript";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\JSON', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-javascript";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\JSON', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "text/x-json";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\JSON', $parser->getFormatClass());

        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }
}
