<?php

namespace Nathanmac\Utilities\Parser\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Parser Facade, supporting Laravel implementations.
 *
 * @package    Nathanmac\Utilities\Parser\Facades
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 *
 * @method     static array payload(string $format = '') Parse the HTTP payload data, autodetect format and return all data in array. Override the format by providing a content type.
 *
 * @method     static array xml(string $payload) XML to Array
 * @method     static array json(string $payload) JSON to Array
 * @method     static array yaml(string $payload) YAML to Array
 * @method     static array querystr(string $payload) Query String to Array
 * @method     static array serialize(string $payload) Serialized Object to Array
 * @method     static array bson(string $payload) BSON to Array
 * @method     static array msgpack(string $payload) MSGPack to Array
 *
 * @method     static array all() Alias to the payload function.
 * @method     static array get(string $key = null, string $default = null) Retrieve an payload item from the payload data, return default item if item not found.
 * @method     static array has(string|array $keys) Determine if the payload contains a non-empty value for a given key.
 * @method     static array only(string|array $keys) Get a subset of the items from the payload data.
 * @method     static array except(string|array $keys) Get all of the input except for a specified array of items.
 */
class Parser extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'Parser'; }
}
