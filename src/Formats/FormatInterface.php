<?php namespace Nathanmac\Utilities\Formats;

interface FormatInterface {

    /**
     * Parse Payload Data
     *
     * @param string $payload
     * @return array
     */
    public function parse($payload);

}
