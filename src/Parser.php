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

    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return array_only($this->payload(), $keys) + array_fill_keys($keys, null);
    }

    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->payload();

        foreach ($keys as $key) array_forget($results, $key);

        return $results;
    }

    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        $results = $this->payload();

        foreach ($keys as $value)
        {
            if (!isset($results[$value]))
                return false;

            if (is_bool($results[$value]))
                return true;

            if ($results[$value] === '')
                return false;
        }
        return true;
    }

    public function get($key = null, $default = null)
    {
        $results = $this->payload();

        if ($this->has($key)){
            return $results[$key];
        }
        return $default;
    }

    /**
     * Alias to the payload function.
     *
     * @return mixed
     */
    public function all()
    {
        return $this->payload();
    }

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

    public function _format()
    {
        if (isset($_SERVER['CONTENT_TYPE']))
        {
            if (isset($this->supported_formats[$_SERVER['CONTENT_TYPE']]))
                return $this->supported_formats[$_SERVER['CONTENT_TYPE']];
        }
        if (isset($_SERVER['HTTP_CONTENT_TYPE']))
        {
            if (isset($this->supported_formats[$_SERVER['HTTP_CONTENT_TYPE']]))
                return $this->supported_formats[$_SERVER['HTTP_CONTENT_TYPE']];
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


/**
 * Helper Functions
 *
 * http://laravel.com/api/4.2/Illuminate/Http/Request.html
 */
if ( ! function_exists('array_only'))
{
    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array  $keys
     * @return array
     */
    function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}

if ( ! function_exists('array_forget'))
{
    /**
     * Remove an array item from a given array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @return void
     */
    function array_forget(&$array, $key)
    {
        $keys = explode('.', $key);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            if ( ! isset($array[$key]) || ! is_array($array[$key]))
            {
                return;
            }

            $array =& $array[$key];
        }

        unset($array[array_shift($keys)]);
    }
}