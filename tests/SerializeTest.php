<?php

require dirname(__FILE__)."/../vendor/autoload.php";

use Nathanmac\Utilities\Parser\Parser;
use \Mockery as m;

class SerializeTest extends PHPUnit_Framework_TestCase {

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

        $parser->shouldReceive('getFormat')
            ->once()
            ->andReturn('serialize');

        $parser->shouldReceive('getPayload')
            ->once()
            ->andReturn('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}');

        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->payload());
    }

    /** @test */
    public function parser_validates_serialized_data()
    {
        $parser = new Parser();
        $this->assertEquals(array('status' => 123, 'message' => 'hello world'), $parser->serialize('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}'));
    }

    /** @test */
    public function parser_empty_serialized_data()
    {
        $parser = new Parser();
        $this->assertEquals(array(), $parser->serialize(""));
    }

    /** @test */
    public function throws_an_exception_when_parsed_serialized_bad_data()
    {
        $parser = new Parser();
        $this->setExpectedException('Exception', 'Failed To Parse Serialized Data');
        $parser->serialize('as|df>ASFBw924hg2=');
    }

    /** @test */
    public function format_detection_serialized()
    {
        $parser = new Parser();
        $_SERVER['HTTP_CONTENT_TYPE'] = "application/vnd.php.serialized";
        $this->assertEquals('serialize', $parser->getFormat());
    }
}
