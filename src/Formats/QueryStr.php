<?php namespace Nathanmac\Utilities\Parser\Formats;

/**
 * Query String Formatter
 *
 * @package    Nathanmac\Utilities\Parser\Formats
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
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
