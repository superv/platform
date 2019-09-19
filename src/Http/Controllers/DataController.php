<?php

namespace SuperV\Platform\Http\Controllers;

use Platform;
use SuperV\Platform\Domains\Resource\Nav\Nav;
use SuperV\Platform\Domains\Resource\Nav\NavGuard;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Composer\Payload;

class DataController extends BaseApiController
{
    public function init()
    {
        $user = auth()->user();
        $userArray = $user->toArray();

        return [
            'data' => [
                'user'         => $userArray,
                'translations' => $this->getTranslations(),
            ],
        ];
    }

    public function nav(\SuperV\Platform\Support\Current $current)
    {
        if (! $port = $current->port()) {
            PlatformException::fail('No registered ports found');
        }

        if (! $portNav = $port->getNavigationSlug()) {
            PlatformException::fail('Current port has no navigation');
        }

        // @TODO.dali
        $nav = (new NavGuard(auth()->user(), Nav::get('acp')))->compose();

        $payload = new Payload([
            'nav' => $nav,
        ]);

        $this->events->fire('nav.composed', $payload);

        return [
            'data' => $payload->toArray(),
        ];
    }

    protected function getTranslations()
    {
        $file = Platform::realPath('resources/lang/'.app()->getLocale().'.json');

        if (! is_readable($file)) {
            return [];
        }

        $string = file_get_contents($file);

        return ['data' => json_decode($string, true), 'hash' => md5($string)];
    }
}