<?php namespace Nathanmac\Utilities;

use Nathanmac\Utilities\Formats\FormatInterface;
use Nathanmac\Utilities\Formats\JSON;
use Nathanmac\Utilities\Formats\QueryStr;
use Nathanmac\Utilities\Formats\Serialize;
use Nathanmac\Utilities\Formats\XML;
use Nathanmac\Utilities\Formats\YAML;

class Parser
{
    private $wildcards = '/^(\*|%|:first|:last|:(index|item)\[\d+\])$/';

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

    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = array();
        foreach ($keys as $key) {
            $results = array_merge_recursive($results, $this->buildArray(explode('.', $key), $this->get($key)));
        }

        return $results;
    }

    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->payload();

        foreach ($keys as $key) {
            $this->removeValue($results, $key);
        }
        return $results;
    }

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

    public function get($key = null, $default = null)
    {
        if ($this->has($key)) {
            return $this->getValueAtKey($key, $this->payload());
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
            if (isset($this->supported_formats[$format]))
                return $this->{$this->supported_formats[$format]}($this->getPayload());
        return $this->{$this->getFormat()}($this->getPayload());
    }

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
    public function parser($payload, FormatInterface $format)
    {
        return $format->parse($payload);
    }

    /* ------------ Helper Methods ------------ */

	public function xml($payload)
    {
        return $this->parser($payload, new XML());
    }

    public function json($payload)
    {
        return $this->parser($payload, new JSON());
    }

    public function serialize($payload)
    {
        return $this->parser($payload, new Serialize());
    }

    public function querystr($payload)
    {
        return $this->parser($payload, new QueryStr());
    }

    public function yaml($payload)
    {
        return $this->parser($payload, new Yaml());
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
