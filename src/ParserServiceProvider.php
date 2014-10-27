<?php namespace Nathanmac\Utilities\Parser;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * ParserServiceProvider, supporting Laravel implementations.
 *
 * @package    Nathanmac\Utilities\Parser
 * @author     Nathan Macnamara <nathan.macnamara@outlook.com>
 * @license    https://github.com/nathanmac/Parser/blob/master/LICENSE.md  MIT
 */
class ParserServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Parser', function($app) {
            return new Parser;
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('nathanmac/parser');

        AliasLoader::getInstance()->alias(
            'Parser',
            'Nathanmac\Utilities\Parser\Facades\Parser'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('Parser');
    }

}
