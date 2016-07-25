<?php

namespace Nathanmac\Utilities\Parser\Formats;

use Nathanmac\Utilities\Parser\Exceptions\ParserException;

/**
 * BSON Formatter
 *
 * @package    Nathanmac\Utilities\Parser\Formats
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class BSON implements FormatInterface
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
        if (function_exists('MongoDB\BSON\toPHP') && ! function_exists('bson_decode')) {
            require_once(__DIR__ . '/BSONPolyfill.php');  //@codeCoverageIgnore
        } elseif ( ! (function_exists('bson_decode') || function_exists('MongoDB\BSON\toPHP'))) {
            throw new ParserException('Failed To Parse BSON - Supporting Library Not Available');  // @codeCoverageIgnore
        }

        if ($payload) {
            $prevHandler = set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
                throw new \Exception($errstr);  // @codeCoverageIgnore
            });

            try {
                $bson = bson_decode(trim($payload, " \t\n\r\x0b"));  // Don't trim \0, as it has valid meaning in BSON
                if ( ! $bson) {
                    throw new \Exception('Unknown error');  // @codeCoverageIgnore
                }
            } catch (\Exception $e) {
                set_error_handler($prevHandler);
                throw new ParserException('Failed To Parse BSON - ' . $e->getMessage());
            }

            set_error_handler($prevHandler);

            return $bson;
        }

        return [];
    }
}
