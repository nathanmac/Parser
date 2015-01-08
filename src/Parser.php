<?php namespace Nathanmac\Utilities\Parser;

use Nathanmac\Utilities\Parser\Formats\BSON;
use Nathanmac\Utilities\Parser\Formats\FormatInterface;
use Nathanmac\Utilities\Parser\Formats\JSON;
use Nathanmac\Utilities\Parser\Formats\QueryStr;
use Nathanmac\Utilities\Parser\Formats\Serialize;
use Nathanmac\Utilities\Parser\Formats\XML;
use Nathanmac\Utilities\Parser\Formats\YAML;

/**
 * Parser Library, designed to parse payload data from various formats to php array.
 *
 * @package    Nathanmac\Utilities\Parser
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class Parser
{
    /**
     * @var string
     */
    private $wildcards = '/^(\*|%|:first|:last|:(index|item)\[\d+\])$/';

    /**
     * @var array Supported Formats
     */
    private $supported_formats = array (
      // XML
	    'application/xml' => 'xml',
	    'text/xml' => 'xml',
      // JSON
	    'application/json' => 'json',
		'application/x-javascript' => 'json',
		'text/javascript' => 'json',
		'text/x-javascript' => 'json',
		'text/x-json' => 'json',
      // BSON
        'application/bson' => 'bson',
      // YAML
	    'text/yaml' => 'yaml',
		'text/x-yaml' => 'yaml',
		'application/yaml' => 'yaml',
		'application/x-yaml' => 'yaml',
      // MISC
		'application/vnd.php.serialized' => 'serialize',
	    'application/x-www-form-urlencoded' => 'querystr'
    );

    /* ------------ Access Methods/Helpers ------------ */

    /**
     * Get a subset of the items from the payload data.
     *
     * @param  string|array $keys
     *
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = array();
        foreach ($keys as $key) {
            $results = array_merge_recursive($results, $this->buildArray(explode('.', $key), $this->get($key)));
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param  string|array  $keys
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->payload();

        foreach ($keys as $key) {
            $this->removeValue($results, $key);
        }
        return $results;
    }

    /**
     * Determine if the payload contains a non-empty value for a given key.
     *
     * @param  string|array $keys
     *
     * @return bool
     */
    public function has($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->payload();

        foreach ($keys as $value)
        {
            if ($this->hasValueAtKey($value, $results) === false)
                return false;
        }
        return true;
    }

    /**
     * Retrieve an payload item from the payload data, return default item if item not found.
     *
     * @param string $key
     * @param string $default
     *
     * @return mixed|null
     */
    public function get($key = null, $default = null)
    {
        if ($this->has($key)) {
            return $this->getValueAtKey($key, $this->payload());
        }
        return $default;
    }

    /**
     * Parse the HTTP payload data, autodetect format and return all data in array.
     *  Override the format by providing a content type.
     *
     * @param string $format
     *
     * @return array
     */
    public function payload($format = '')
    {
        if (!empty($format))
            if (isset($this->supported_formats[$format]))
                return $this->{$this->supported_formats[$format]}($this->getPayload());
        return $this->{$this->getFormat()}($this->getPayload());
    }

    /**
     * Alias to the payload function.
     *
     * @return array
     */
    public function all()
    {
        return $this->payload();
    }

    /**
     * Autodetect the payload data type using content-type value.
     *
     * @return string Return the short format code (xml, json, ...).
     */
    public function getFormat()
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

    /**
     * Return the payload data from the HTTP post request.
     *
     * @return string
     */
    protected function getPayload()
    {
        return file_get_contents('php://input');
    }

    /**
     * Parse payload string using given formatter.
     *
     * @param string $payload
     * @param FormatInterface $format
     *
     * @return array
     */
    public function parse($payload, FormatInterface $format)
    {
        return $format->parse($payload);
    }

    /* ------------ Helper Methods ------------ */

    /**
     * XML parser, helper function.
     *
     * @param $payload
     *
     * @return array
     *
     * @throws Exceptions\ParserException
     */
    public function xml($payload)
    {
        return $this->parse($payload, new XML());
    }

    /**
     * JSON parser, helper function.
     *
     * @param $payload
     *
     * @return array
     *
     * @throws Exceptions\ParserException
     */
    public function json($payload)
    {
        return $this->parse($payload, new JSON());
    }

    /**
     * BSON parser, helper function.
     *
     * @param $payload
     *
     * @return array
     *
     * @throws Exceptions\ParserException
     */
    public function bson($payload)
    {
        return $this->parse($payload, new BSON());
    }

    /**
     * Serialized Data parser, helper function.
     *
     * @param $payload
     *
     * @return array
     *
     * @throws Exceptions\ParserException
     */
    public function serialize($payload)
    {
        return $this->parse($payload, new Serialize());
    }

    /**
     * Query String parser, helper function.
     *
     * @param $payload
     *
     * @return array
     */
    public function querystr($payload)
    {
        return $this->parse($payload, new QueryStr());
    }

    /**
     * YAML parser, helper function.
     *
     * @param $payload
     *
     * @return array
     *
     * @throws Exceptions\ParserException
     */
    public function yaml($payload)
    {
        return $this->parse($payload, new Yaml());
    }

    /* ------------ Construction Methods ------------ */

    /**
     * Return a value from the array identified from the key.
     *
     * @param $key
     * @param $data
     * @return mixed
     */
    private function getValueAtKey($key, $data)
    {
        $keys = explode('.', $key);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            // Wildcard Key
            if (preg_match($this->wildcards, $key) && is_array($data) && !empty($data)) {
                // Shift the first item of the array
                if (preg_match('/^:(index|item)\[\d+\]$/', $key)) {
                    for ($x = substr($key, 7, -1); $x >= 0; $x--) {
                        if (empty($data))
                            return false;
                        $item = array_shift($data);
                    }
                } else if ($key == ':last') {
                    $item = array_pop($data);
                } else {
                    $item = array_shift($data);
                }
                $data =& $item;

            } else {
                if (!isset($data[$key]) || !is_array($data[$key]))
                    return false;

                $data =& $data[$key];
            }
        }

        // Return value
        $key = array_shift($keys);
        if (preg_match($this->wildcards, $key)) {
            if (preg_match('/^:(index|item)\[\d+\]$/', $key)) {
                for ($x = substr($key, 7, -1); $x >= 0; $x--) {
                    if (empty($data))
                        return false;
                    $item = array_shift($data);
                }
                return $item;
            } else if ($key == ':last') {
                return array_pop($data);
            }
            return array_shift($data); // First Found
        }
        return ($data[$key]);
    }

    /**
     * Array contains a value identified from the key, returns bool
     *
     * @param $key
     * @param $data
     * @return bool
     */
    private function hasValueAtKey($key, $data)
    {
        $keys = explode('.', $key);

        while (count($keys) > 0)
        {
            $key = array_shift($keys);

            // Wildcard Key
            if (preg_match($this->wildcards, $key) && is_array($data) && !empty($data)) {
                // Shift the first item of the array
                if (preg_match('/^:(index|item)\[\d+\]$/', $key)) {
                    for ($x = substr($key, 7, -1); $x >= 0; $x--) {
                        if (empty($data))
                            return false;
                        $item = array_shift($data);
                    }
                } else if ($key == ':last') {
                    $item = array_pop($data);
                } else {
                    $item = array_shift($data);
                }
                $data =& $item;

            } else {
                if (!isset($data[$key]))
                    return false;

                if (is_bool($data[$key]))
                    return true;

                if ($data[$key] === '')
                    return false;

                $data =& $data[$key];
            }
        }
        return true;
    }

    /**
     * Build the array structure for value.
     *
     * @param $route
     * @param null $data
     * @return array|null
     */
    private function buildArray($route, $data = null)
    {
        $key = array_pop($route);
        $data = array($key => $data);
        if (count($route) == 0)
        {
            return $data;
        }
        return $this->buildArray($route, $data);
    }

    /**
     * Remove a value identified from the key
     *
     * @param $array
     * @param $key
     */
    private function removeValue(&$array, $key)
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
