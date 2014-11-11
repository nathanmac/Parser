<?php namespace Nathanmac\Utilities\Parser\Formats;

use Nathanmac\Utilities\Parser\Exceptions\ParserException;

/**
 * BSON Formatter
 *
 * @package    Nathanmac\Utilities\Parser\Formats
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class BSON implements FormatInterface {

    /**
     * Parse Payload Data
     *
     * @param string $payload
     *
     * @return array
     *
     * @throws ParserException
     */
    public function parse($payload)
    {
        if (function_exists('bson_decode')) {
            if ($payload) {
                $bson = bson_decode(trim($payload));
                if (!$bson)
                    throw new ParserException('Failed To Parse BSON');
                return $bson;
            }
            return array();
        } else {
            throw new ParserException('Failed To Parse BSON - Supporting Library Not Available');
        }
    }

}
