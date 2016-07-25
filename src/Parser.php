<?php

namespace Nathanmac\Utilities\Parser;

use Nathanmac\Utilities\Parser\Formats\BSON;
use Nathanmac\Utilities\Parser\Formats\FormatInterface;
use Nathanmac\Utilities\Parser\Formats\JSON;
use Nathanmac\Utilities\Parser\Formats\MSGPack;
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
    private $supported_formats = [
      // XML
        'application/xml' => 'Nathanmac\Utilities\Parser\Formats\XML',
        'text/xml'        => 'Nathanmac\Utilities\Parser\Formats\XML',
        'xml'             => 'Nathanmac\Utilities\Parser\Formats\XML',
      // JSON
        'application/json'         => 'Nathanmac\Utilities\Parser\Formats\JSON',
        'application/x-javascript' => 'Nathanmac\Utilities\Parser\Formats\JSON',
        'text/javascript'          => 'Nathanmac\Utilities\Parser\Formats\JSON',
        'text/x-javascript'        => 'Nathanmac\Utilities\Parser\Formats\JSON',
        'text/x-json'              => 'Nathanmac\Utilities\Parser\Formats\JSON',
        'json'                     => 'Nathanmac\Utilities\Parser\Formats\JSON',
      // BSON
        'application/bson' => 'Nathanmac\Utilities\Parser\Formats\BSON',
        'bson'             => 'Nathanmac\Utilities\Parser\Formats\BSON',
      // YAML
        'text/yaml'          => 'Nathanmac\Utilities\Parser\Formats\YAML',
        'text/x-yaml'        => 'Nathanmac\Utilities\Parser\Formats\YAML',
        'application/yaml'   => 'Nathanmac\Utilities\Parser\Formats\YAML',
        'application/x-yaml' => 'Nathanmac\Utilities\Parser\Formats\YAML',
        'yaml'               => 'Nathanmac\Utilities\Parser\Formats\YAML',
      // MSGPACK
        'application/msgpack'   => 'Nathanmac\Utilities\Parser\Formats\MSGPack',
        'application/x-msgpack' => 'Nathanmac\Utilities\Parser\Formats\MSGPack',
        'msgpack'               => 'Nathanmac\Utilities\Parser\Formats\MSGPack',
      // MISC
        'application/vnd.php.serialized'    => 'Nathanmac\Utilities\Parser\Formats\Serialize',
        'serialize'                         => 'Nathanmac\Utilities\Parser\Formats\Serialize',
        'application/x-www-form-urlencoded' => 'Nathanmac\Utilities\Parser\Formats\QueryStr',
        'querystr'                          => 'Nathanmac\Utilities\Parser\Formats\QueryStr',
    ];

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

        $results = [];
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

        foreach ($keys as $value) {
            if ($this->hasValueAtKey($value, $results) === false) {
                return false;
            }
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
     * Mask input data with a given mapping.
     *
     * @param array $mask
     *
     * @return array
     */
    public function mask(array $mask)
    {
        $keys = [];
        foreach ($mask as $key => $value) {
            $keys[] = $key . (is_array($value) ? $this->processMask($value) : '');
        }

        return $this->only($keys);
    }

    /**
     * Recursive processor for processing user masks.
     *
     * @param array $mask
     *
     * @return string
     */
    private function processMask($mask)
    {
        foreach ($mask as $key => $value) {
            return '.' . $key . (is_array($value) ? $this->processMask($value) : '');
        }
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
        $class = $this->getFormatClass($format);
        return $this->parse($this->getPayload(), new $class);
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
     * @return string Return the name of the formatter class.
     */
    public function getFormatClass($format = '')
    {
        if ( ! empty($format)) {
            return $this->processContentType($format);
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $type = $this->processContentType($_SERVER['CONTENT_TYPE']);
            if ($type !== false) {
                return $type;
            }
        }

        if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
            $type = $this->processContentType($_SERVER['HTTP_CONTENT_TYPE']);
            if ($type !== false) {
                return $type;
            }
        }

        return 'Nathanmac\Utilities\Parser\Formats\JSON';
    }

    /**
     * Process the content-type values
     *
     * @param string $contentType Content-Type raw string
     *
     * @return bool|string
     */
    private function processContentType($contentType)
    {
        foreach (explode(';', $contentType) as $type) {
            $type = strtolower(trim($type));
            if (isset($this->supported_formats[$type])) {
                return $this->supported_formats[$type];
            }
        }

        return false;
    }

    /**
     * Return the payload data from the HTTP post request.
     *
     * @codeCoverageIgnore
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

    /* ------------ Format Registration Methods ------------ */

    /**
     * Register Format Class.
     *
     * @param $format
     * @param $class
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public function registerFormat($format, $class)
    {
        if ( ! class_exists($class)) {
            throw new \InvalidArgumentException("Parser formatter class {$class} not found.");
        }
        if ( ! is_a($class, 'Nathanmac\Utilities\Parser\Formats\FormatInterface', true)) {
            throw new \InvalidArgumentException('Parser formatters must implement the Nathanmac\Utilities\Parser\Formats\FormatInterface interface.');
        }

        $this->supported_formats[$format] = $class;

        return $this;
    }

    /* ------------ Helper Methods ------------ */

    /**
     * XML parser, helper function.
     *
     * @param $payload
     *
     * @throws Exceptions\ParserException
     *
     * @return array
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
     * @throws Exceptions\ParserException
     * @return array
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
     * @throws Exceptions\ParserException
     *
     * @return array
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
     * @throws Exceptions\ParserException
     *
     * @return array
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
     * @throws Exceptions\ParserException
     *
     * @return array
     */
    public function yaml($payload)
    {
        return $this->parse($payload, new Yaml());
    }

    /**
     * MSGPack parser, helper function.
     *
     * @param $payload
     *
     * @throws Exceptions\ParserException
     *
     * @return array
     */
    public function msgpack($payload)
    {
        return $this->parse($payload, new MSGPack());
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

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // Wildcard Key
            if (preg_match($this->wildcards, $key) && is_array($data) && ! empty($data)) {
                // Shift the first item of the array
                if (preg_match('/^:(index|item)\[\d+\]$/', $key)) {
                    for ($x = substr($key, 7, -1); $x >= 0; $x--) {
                        if (empty($data)) {
                            return false;
                        }
                        $item = array_shift($data);
                    }
                } elseif ($key == ':last') {
                    $item = array_pop($data);
                } else {
                    $item = array_shift($data);
                }
                $data =& $item;
            } else {
                if ( ! isset($data[$key]) || ! is_array($data[$key])) {
                    return false;
                }

                $data =& $data[$key];
            }
        }

        // Return value
        $key = array_shift($keys);
        if (preg_match($this->wildcards, $key)) {
            if (preg_match('/^:(index|item)\[\d+\]$/', $key)) {
                for ($x = substr($key, 7, -1); $x >= 0; $x--) {
                    if (empty($data)) {
                        return false;
                    }
                    $item = array_shift($data);
                }
                return $item;
            } elseif ($key == ':last') {
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

        while (count($keys) > 0) {
            $key = array_shift($keys);

            // Wildcard Key
            if (preg_match($this->wildcards, $key) && is_array($data) && ! empty($data)) {
                // Shift the first item of the array
                if (preg_match('/^:(index|item)\[\d+\]$/', $key)) {
                    for ($x = substr($key, 7, -1); $x >= 0; $x--) {
                        if (empty($data)) {
                            return false;
                        }
                        $item = array_shift($data);
                    }
                } elseif ($key == ':last') {
                    $item = array_pop($data);
                } else {
                    $item = array_shift($data);
                }
                $data =& $item;
            } else {
                if ( ! isset($data[$key])) {
                    return false;
                }

                if (is_bool($data[$key])) {
                    return true;
                }

                if ($data[$key] === '') {
                    return false;
                }

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
        $key  = array_pop($route);
        $data = [$key => $data];
        if (count($route) == 0) {
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

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if ( ! isset($array[$key]) || ! is_array($array[$key])) {
                return;
            }

            $array =& $array[$key];
        }

        unset($array[array_shift($keys)]);
    }
}
