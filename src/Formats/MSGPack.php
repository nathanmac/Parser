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
     * @return array
     *
     * @throws ParserException
     */
    public function parse($payload)
    {
        if (function_exists('msgpack_unpack')) {
            if ($payload) {
                $msg = msgpack_unpack(trim($payload));
                if (! $msg)
                    throw new ParserException('Failed To Parse MSGPack');
                return $msg;
            }
            return array();
        }

        throw new ParserException('Failed To Parse MSGPack - Supporting Library Not Available');
    }
}
