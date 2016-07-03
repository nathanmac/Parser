<?php

namespace Nathanmac\Utilities\Parser\Tests;

use Nathanmac\Utilities\Parser\Parser;

class MSGPackTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function parser_validates_msgpack_data()
    {
        if (function_exists('msgpack_unpack')) {
            $expected = ['status' => 123, 'message' => 'hello world'];
            $payload  = msgpack_pack($expected);

            $parser = new Parser();
            $this->assertEquals($expected, $parser->msgpack($payload));
        }
    }

    /** @test */
    public function parser_empty_msgpack_data()
    {
        if (function_exists('msgpack_unpack')) {
            $parser = new Parser();
            $this->assertEquals([], $parser->msgpack(""));
        }
    }

    /** @test */
    public function throw_an_exception_when_msgpack_library_not_loaded()
    {
        if ( ! function_exists('msgpack_unpack')) {
            $this->setExpectedException('Exception', 'Failed To Parse MSGPack - Supporting Library Not Available');

            $parser = new Parser();
            $this->assertEquals([], $parser->msgpack(""));
        }
    }

    /** @test */
    public function throws_an_exception_when_parsed_msgpack_bad_data()
    {
        if (function_exists('msgpack_unpack')) {
            $parser = new Parser();
            $this->setExpectedException('Exception', 'Failed To Parse MSGPack');
            $parser->msgpack('as|df>ASFBw924hg2=');
        }
    }

    /** @test */
    public function format_detection_msgpack()
    {
        $parser = new Parser();
        
        $_SERVER['HTTP_CONTENT_TYPE'] = "application/msgpack";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\MSGPack', $parser->getFormatClass());

        $_SERVER['HTTP_CONTENT_TYPE'] = "application/x-msgpack";
        $this->assertEquals('Nathanmac\Utilities\Parser\Formats\MSGPack', $parser->getFormatClass());

        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }
}
