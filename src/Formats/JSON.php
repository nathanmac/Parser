<?php namespace Nathanmac\Utilities\Formats;

use Nathanmac\Utilities\Exceptions\ParserException;

/**
 * JSON Formatter
 *
 * @package    Nathanmac\Utilities\Formats
 * @author     Nathan Macnamarar <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class JSON implements FormatInterface {

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
        if ($payload) {
            $json = json_decode(trim($payload), true);
            if (!$json)
                throw new ParserException('Failed To Parse JSON');
            return $json;
        }
        return array();
    }

}
