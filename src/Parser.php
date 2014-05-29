<?php namespace Nathanmac\ParserUtility;

use Symfony\Component\Yaml\Yaml;
use Exception;

class Parser
{
    private $supported_formats = array (
      // XML
	    'application/xml' => 'xml',
	    'text/xml' => 'xml',
      // BSON
        'application/bson' => 'bson',
      // JSON
	    'application/json' => 'json',
		'application/x-javascript' => 'json',
		'text/javascript' => 'json',
		'text/x-javascript' => 'json',
		'text/x-json' => 'json',
      // YAML
	    'text/yaml' => 'yaml',
		'text/x-yaml' => 'yaml',
		'application/yaml' => 'yaml',
		'application/x-yaml' => 'yaml',
      // MISC
		'application/vnd.php.serialized' => 'serialize',
	    'application/x-www-form-urlencoded' => 'querystr'
    );

    public function payload($format = false)
    {
        if ($format !== false)
        {
            if (isset($this->supported_formats[$format]))
            {
                return $this->{$this->supported_formats[$format]}($this->_payload());
            }
            throw new ParserException('Invalid Or Unsupported Format');
        }
        return $this->{$this->_format()}($this->_payload());
    }

    protected function _format()
    {
        if (isset($_SERVER['CONTENT_TYPE']))
        {
            if (isset($this->supported_formats[$_SERVER['CONTENT_TYPE']]))
                return $this->supported_formats[$_SERVER['CONTENT_TYPE']];
        }
        return 'json';
    }

    protected function _payload()
    {
        return file_get_contents('php://input');
    }

	public function xml($string)
    {
        if ($string)
        {
            $xml = @simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);
            if(!$xml)
            {
                throw new ParserException('Failed To Parse XML');
            }
            return json_decode(json_encode((array) $xml), 1);   // Work around to accept xml input
        }
        return array();
    }

    public function json($string)
    {
        if ($string)
        {
            $json = json_decode(trim($string), true);
            if (!$json)
                throw new ParserException('Failed To Parse JSON');
            return $json;
        }
        return array();
    }

    public function serialize($string)
    {
        if ($string)
        {
            $serial = @unserialize(trim($string));
            if (!$serial)
                throw new ParserException('Failed To Parse Serialized Data');
            return $serial;
        }
        return array();
    }

    public function querystr($string)
    {
        if ($string)
        {
            @parse_str(trim($string), $querystr);
            if (!$querystr)
                throw new ParserException('Failed To Parse Query String');
            return $querystr;
        }
        return array();
    }

    public function yaml($string)
    {
        if ($string)
        {
            try {
                return Yaml::parse(trim(preg_replace('/\t+/', '', $string)));
            } catch (Exception $ex) {
                throw new ParserException('Failed To Parse YAML');
            }
        }
        return array();
    }
}

class ParserException extends Exception {}