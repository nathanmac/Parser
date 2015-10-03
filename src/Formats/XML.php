<?php

namespace Nathanmac\Utilities\Parser\Formats;

use Nathanmac\Utilities\Parser\Exceptions\ParserException;

/**
 * XML Formatter
 *
 * @package    Nathanmac\Utilities\Parser\Formats
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class XML implements FormatInterface
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
        if ($payload) {
            try {
                $xml = simplexml_load_string($payload, 'SimpleXMLElement', LIBXML_NOCDATA);

                // Fix for empty values in XML
                $json = json_encode((array) $xml);
                $json = str_replace(':{}',':null', $json);
                $json = str_replace(':[]',':null', $json);
                return json_decode($json, 1);   // Work around to accept xml input
            } catch (\Exception $ex) {
                throw new ParserException('Failed To Parse XML');
            }
        }
        
        return array();
    }
}
