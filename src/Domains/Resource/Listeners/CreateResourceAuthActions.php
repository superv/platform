<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Auth\Access\Action;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;

class CreateResourceAuthActions
{
    public function handle(ResourceCreatedEvent $event)
    {
        $resourceEntry = $event->resourceEntry;
        $namespace = $resourceEntry->getNamespace();

        if ($namespace === 'platform') {
            return;
        }

        $identifier = $namespace.'.'.$resourceEntry->getName();

        $this->createAction($namespace, sprintf("%s", $identifier));
        $this->createAction($namespace, sprintf("%s.*", $identifier));
        $this->createAction($identifier, sprintf("%s.fields.*", $identifier));
        $this->createAction($identifier, sprintf("%s.actions.*", $identifier));

        collect(['view', 'create', 'edit', 'list'])
            ->map(function ($action) use ($identifier) {
                $this->createAction($identifier, sprintf("%s.actions:%s", $identifier, $action));
            });
    }

    public function createAction($namespace, $identifier): void
    {
        Action::query()->create([
            'namespace' => $namespace,
            'slug'      => $identifier,
        ]);
    }
}