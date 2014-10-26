<?php namespace Nathanmac\Utilities\Formats;

use Nathanmac\Utilities\Exceptions\ParserException;

/**
 * YAML Formatter
 *
 * @package    Nathanmac\Utilities\Formats
 * @author     Nathan Macnamarar <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class YAML implements FormatInterface {

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
                return \Symfony\Component\Yaml\Yaml::parse(trim(preg_replace('/\t+/', '', $payload)));
            } catch (\Exception $ex) {
                throw new ParserException('Failed To Parse YAML');
            }
        }
        return array();
    }

}
