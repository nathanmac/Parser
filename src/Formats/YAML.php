<?php

namespace Nathanmac\Utilities\Parser\Formats;

use Nathanmac\Utilities\Parser\Exceptions\ParserException;
use Symfony\Component\Yaml\Yaml as SFYaml;

/**
 * YAML Formatter
 *
 * @package    Nathanmac\Utilities\Parser\Formats
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class YAML implements FormatInterface
{
    /**
     * Parse Payload Data
     *
     * @param string $payload
     *
     * @throws ParserException
     * @return array
     *
     */
    public function parse($payload)
    {
        if ($payload) {
            try {
                $flags = (defined('Symfony\Component\Yaml\Yaml::PARSE_DATETIME')) ? (SFYaml::PARSE_EXCEPTION_ON_INVALID_TYPE | SFYaml::PARSE_DATETIME) : true;
                return SFYaml::parse(trim(preg_replace('/\t+/', '', $payload)), $flags);
            } catch (\Exception $ex) {
                throw new ParserException('Failed To Parse YAML');
            }
        }

        return [];
    }
}
