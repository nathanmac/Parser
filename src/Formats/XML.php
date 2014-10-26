<?php namespace Nathanmac\Utilities\Formats;

use Nathanmac\Utilities\Exceptions\ParserException;

class XML implements FormatInterface {

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
        if ($payload)
        {
            try {
                $xml = simplexml_load_string($payload, 'SimpleXMLElement', LIBXML_NOCDATA);
                return json_decode(json_encode((array) $xml), 1);   // Work around to accept xml input
            } catch (\Exception $ex) {
                throw new ParserException('Failed To Parse XML');
            }
        }
        return array();
    }

}
