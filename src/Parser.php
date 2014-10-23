<?php namespace Nathanmac\ParserUtility;

use Exception;

class Parser
{
    private $wildcards = '/^(\*|%|:first|:last|:(index|item)\[\d+\])$/';

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

        $results = array();
        foreach ($keys as $key) {
            $results = array_merge_recursive($results, $this->_buildArray(explode('.', $key), $this->get($key)));
        }

        return $results;
    }

    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->payload();

        foreach ($keys as $key) {
            $this->_removeValue($results, $key);
        }
        return $results;
    }

    public function has($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->payload();

        foreach ($keys as $value)
        {
            if ($this->_hasValue($value, $results) === false)
                return false;
        }
        return true;
    }

    public function get($key = null, $default = null)
    {
        $results = $this->payload();

        if ($this->has($key)) {
            return $this->_getValue($key, $results);
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
                return $this->{$this->supported_formats[$format]}($this->_payload());
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
        return $this->parse($string, new XML());
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

    /**
     * Return a value from the array identified from the key.
     *
     * @param $key
     * @param $data
     * @return mixed
     */
    private function _getValue($key, $data)
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
    private function _hasValue($key, $data)
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
    private function _buildArray($route, $data = null)
    {
        $key = array_pop($route);
        $data = array($key => $data);
        if (count($route) == 0)
        {
            return $data;
        }
        return $this->_buildArray($route, $data);
    }

    /**
     * Remove a value identified from the key
     *
     * @param $array
     * @param $key
     */
    private function _removeValue(&$array, $key)
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

class ParserException extends Exception {}
