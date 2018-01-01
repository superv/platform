<?php

namespace SuperV\Platform\Domains\UI\DeprecatedForm;

use Symfony\Component\Form\Forms;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Form\FormFactory;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use SuperV\Platform\Domains\UI\DeprecatedForm\Extension\SessionExtension;
use SuperV\Platform\Domains\UI\DeprecatedForm\Extension\Http\HttpExtension;
use SuperV\Platform\Domains\UI\DeprecatedForm\Extension\FormValidatorExtension;
use SuperV\Platform\Domains\UI\DeprecatedForm\Extension\FormDefaultsTypeExtension;
use SuperV\Platform\Domains\UI\DeprecatedForm\Extension\Validation\ValidationTypeExtension;

class FormServiceProvider extends ServiceProvider
{
    public function register()
    {
        $configPath = __DIR__.'/../../../../resources/config/form.php';
        $this->mergeConfigFrom($configPath, 'form');

        $this->app->singleton(TwigRendererEngine::class, function ($app) {
            $theme = (array) $app['config']->get('form.theme', 'bootstrap_3_layout.html.twig');

            return new TwigRendererEngine($theme);
        });

        $this->app->singleton(TwigRenderer::class, function ($app) {
            $renderer = $app->make(TwigRendererEngine::class);

            return new TwigRenderer($renderer);
        });

        $this->app->alias(TwigRenderer::class, FormRendererInterface::class);

        $this->app->bind('form.type.extensions', function ($app) {
            return [
                new FormDefaultsTypeExtension($app['config']->get('form.defaults', [])),
                new ValidationTypeExtension($app['validator']),
            ];
        });
        $this->app->bind('form.type.guessers', function ($app) {
            return [];
        });

        $this->app->bind('form.extensions', function ($app) {
            return [
                new SessionExtension(),
                new HttpExtension(),
                new FormValidatorExtension(),
            ];
        });

        $this->app->bind('form.resolved_type_factory', function () {
            return new ResolvedFormTypeFactory();
        });

        $this->app->singleton(FormFactory::class, function ($app) {
            return Forms::createFormFactoryBuilder()
                        ->addExtensions($app['form.extensions'])
                        ->addTypeExtensions($app['form.type.extensions'])
                        ->setResolvedTypeFactory($app['form.resolved_type_factory'])
                        ->getFormFactory();
        });
        $this->app->alias(FormFactory::class, 'form.factory');
        $this->app->alias(FormFactory::class, FormFactoryInterface::class);
    }

    public function boot()
    {
        $configPath = __DIR__.'/../../../../resources/config/form.php';
        $this->publishes([$configPath => config_path('form.php')], 'config');

        $twig = $this->app->make(\Twig_Environment::class);

        $loader = $twig->getLoader();

        // If the loader is not already a chain, make it one
        if (! $loader instanceof \Twig_Loader_Chain) {
            $loader = new \Twig_Loader_Chain([$loader]);
            $twig->setLoader($loader);
        }

        $path = __DIR__.'/../../../../resources/views/form';
        $loader->addLoader(new \Twig_Loader_Filesystem($path));

        /** @var TwigRenderer $renderer */
        $renderer = $this->app->make(TwigRenderer::class);
        $renderer->setEnvironment($twig);

        $twig->addRuntimeLoader(new \Twig_FactoryRuntimeLoader([
            TwigRenderer::class => function () {
                return $this->app->make(TwigRenderer::class);
            },
        ]));

        // Add the extension
        $twig->addExtension(new FormExtension());

        // trans filter is used in the forms
        $twig->addFilter(new \Twig_SimpleFilter('trans', 'trans'));

        // csrf_token needs to be replaced for Laravel
        $twig->addFunction(new \Twig_SimpleFunction('csrf_token', 'csrf_token'));
    }
}
