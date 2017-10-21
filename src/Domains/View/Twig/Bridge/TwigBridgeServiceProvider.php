<?php

/**
 * This file is part of the TwigBridge package.
 *
 * @copyright Robert Crowe <hello@vivalacrowe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SuperV\Platform\Domains\View\Twig\Bridge;

use Illuminate\View\ViewServiceProvider;
use InvalidArgumentException;
use SuperV\Platform\Domains\View\Twig\Bridge\Command\Clean;
use SuperV\Platform\Domains\View\Twig\Bridge\Command\Lint;
use SuperV\Platform\Domains\View\Twig\Bridge\Command\TwigBridge;
use Twig_Loader_Array;
use Twig_Loader_Chain;

/**
 * Bootstrap Laravel TwigBridge.
 *
 * You need to include this `ServiceProvider` in your app.php file:
 *
 * <code>
 *     'providers' => [
 *         'TwigBridge\ServiceProvider'
 *     ];
 * </code>
 */
class TwigBridgeServiceProvider extends ViewServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerCommands();
        $this->registerOptions();
        $this->registerLoaders();
        $this->registerEngine();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerExtension();
    }

    /**
     * Load the configuration files and allow them to be published.
     *
     * @return void
     */
    protected function loadConfiguration()
    {
        $configPath = base_path(superv('platform')->getConfigPath('twigbridge.php'));

        $this->publishes([$configPath => config_path('twigbridge.php')], 'config');

        $this->mergeConfigFrom($configPath, 'twigbridge');
    }

    /**
     * Register the Twig extension in the Laravel View component.
     *
     * @return void
     */
    protected function registerExtension()
    {
        $this->app['view']->addExtension(
            $this->app['twig.extension'],
            'twig',
            function () {
                return $this->app['twig.engine'];
            }
        );
    }

    /**
     * Register console command bindings.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands(
            TwigBridge::class,
            Clean::class,
            Lint::class
        );
    }

    /**
     * Register Twig config option bindings.
     *
     * @return void
     */
    protected function registerOptions()
    {
        $this->app->bindIf('twig.extension', function () {
            return config('platform::twigbridge.twig.extension');
        });

        $this->app->bindIf('twig.options', function () {
            $options = config('platform::twigbridge.twig.environment', []);

            // Check whether we have the cache path set
            if (! isset($options['cache']) || is_null($options['cache'])) {
                // No cache path set for Twig, lets set to the Laravel views storage folder
                $options['cache'] = storage_path('framework/views/twig');
            }

            return $options;
        });

        $this->app->bindIf('twig.extensions', function () {
            $load = config('platform::twigbridge.extensions.enabled', []);

            // Is debug enabled?
            // If so enable debug extension
            $options = $this->app['twig.options'];
            $isDebug = (bool)(isset($options['debug'])) ? $options['debug'] : false;

            if ($isDebug) {
                array_unshift($load, 'Twig_Extension_Debug');
            }

            return $load;
        });

        $this->app->bindIf('twig.lexer', function () {
            return null;
        });
    }

    /**
     * Register Twig loader bindings.
     *
     * @return void
     */
    protected function registerLoaders()
    {
        // The array used in the ArrayLoader
        $this->app->bindIf('twig.templates', function () {
            return [];
        });

        $this->app->bindIf('twig.loader.array', function ($app) {
            return new Twig_Loader_Array($app['twig.templates']);
        });

        $this->app->bindIf('twig.loader.viewfinder', function () {
            return new Twig\Loader(
                $this->app['files'],
                $this->app['view']->getFinder(),
                $this->app['twig.extension']
            );
        });

        $this->app->bindIf(
            'twig.loader',
            function () {
                return new Twig_Loader_Chain([
                    $this->app['twig.loader.array'],
                    $this->app['twig.loader.viewfinder'],
                ]);
            },
            true
        );
    }

    /**
     * Register Twig engine bindings.
     *
     * @return void
     */
    protected function registerEngine()
    {
        $this->app->bindIf(
            'twig',
            function () {
                $extensions = $this->app['twig.extensions'];
                $lexer = $this->app['twig.lexer'];
                $twig = new Bridge(
                    $this->app['twig.loader'],
                    $this->app['twig.options'],
                    $this->app
                );

                // Instantiate and add extensions
                foreach ($extensions as $extension) {
                    // Get an instance of the extension
                    // Support for string, closure and an object
                    if (is_string($extension)) {
                        try {
                            $extension = $this->app->make($extension);
                        } catch (\Exception $e) {
                            throw new InvalidArgumentException(
                                "Cannot instantiate Twig extension '$extension': ".$e->getMessage()
                            );
                        }
                    } elseif (is_callable($extension)) {
                        $extension = $extension($this->app, $twig);
                    } elseif (! is_a($extension, 'Twig_Extension')) {
                        throw new InvalidArgumentException('Incorrect extension type');
                    }

                    $twig->addExtension($extension);
                }

                // Set lexer
                if (is_a($lexer, 'Twig_LexerInterface')) {
                    $twig->setLexer($lexer);
                }

                return $twig;
            },
            true
        );

        $this->app->alias('twig', 'Twig_Environment');
        $this->app->alias('twig', 'SuperV\Platform\Domains\View\Twig\Bridge\Bridge');

        $this->app->bindIf('twig.compiler', function () {
            return new Engine\Compiler($this->app['twig']);
        });

        $this->app->bindIf('twig.engine', function () {
            return new Engine\Twig(
                $this->app['twig.compiler'],
                $this->app['twig.loader.viewfinder'],
                config('platform::twigbridge.twig.globals', [])
            );
        });
    }
}
