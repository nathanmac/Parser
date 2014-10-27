<?php namespace Nathanmac\Utilities\Parser\Formats;

/**
 * Formatter Interface
 *
 * @package    Nathanmac\Utilities\Parser\Formats
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
interface FormatInterface {

    /**
     * Parse Payload Data
     *
     * @param string $payload
     * @return array
     */
    public function parse($payload);

}
