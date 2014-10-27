<?php namespace Nathanmac\Utilities\Parser\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Parser Facade, supporting Laravel implementations.
 *
 * @package    Nathanmac\Utilities\Parser\Facades
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class Parser extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'Parser'; }

}
