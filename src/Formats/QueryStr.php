<?php namespace Nathanmac\Utilities\Formats;

/**
 * Query String Formatter
 *
 * @package    Nathanmac\Utilities\Formats
 * @author     Nathan Macnamarar <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class QueryStr implements FormatInterface {

    /**
     * Parse Payload Data
     *
     * @param string $payload
     *
     * @return array
     */
    public function parse($payload)
    {
        if ($payload)
        {
            parse_str(trim($payload), $querystr);
            return $querystr;
        }
        return array();
    }

}
