<?php namespace Nathanmac\Utilities\Formats;

use Nathanmac\Utilities\Exceptions\ParserException;

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
