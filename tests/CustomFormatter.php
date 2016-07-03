<?php

namespace Nathanmac\Utilities\Parser\Tests;

use Nathanmac\Utilities\Parser\Formats\FormatInterface;

/**
 * Custom Formatter
 */
class CustomFormatter implements FormatInterface
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
        $payload; // Raw payload data

        $output = [$payload]; // Process raw payload data to array

        return $output; // return array parsed data
    }
}
