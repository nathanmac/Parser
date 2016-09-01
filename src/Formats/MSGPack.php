<?php

namespace Nathanmac\Utilities\Parser\Formats;

use Nathanmac\Utilities\Parser\Exceptions\ParserException;

/**
 * MSGPack Formatter
 *
 * @package    Nathanmac\Utilities\Parser\Formats
 * @author     Nathan Macnamara <hola@nathanmac.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class MSGPack implements FormatInterface
{
    /**
     * Parse Payload Data
     *
     * @param string $payload
     *
     * @throws ParserException
     *
     * @return array
     */
    public function parse($payload)
    {
        if (function_exists('msgpack_unpack')) {
            if ($payload) {
                $prevHandler = set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
                    throw new \Exception($errstr);  // @codeCoverageIgnore
                });

                try {
                    $msg = msgpack_unpack(trim($payload));
                    if ( ! $msg) {
                        throw new \Exception('Unknown error');  // @codeCoverageIgnore
                    }
                } catch (\Exception $e) {
                    set_error_handler($prevHandler);
                    throw new ParserException('Failed To Parse MSGPack - ' . $e->getMessage());
                }

                set_error_handler($prevHandler);

                return $msg;
            }
            return [];
        }

        throw new ParserException('Failed To Parse MSGPack - Supporting Library Not Available');  // @codeCoverageIgnore
    }
}
