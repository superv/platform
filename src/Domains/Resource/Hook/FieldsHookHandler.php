<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Contracts\Dispatcher;

class FieldsHookHandler implements \SuperV\Platform\Domains\Resource\Hook\Contracts\HookHandlerInterface
{
    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $map = [
        'resolved' => null,
    ];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function hook(string $identifier, string $hookHandler, string $subKey = null)
    {
        foreach ($this->map as $eventType => $contract) {
            $eventName = sprintf("%s.fields:*.events:%s", $identifier, $eventType);
            $this->dispatcher->listen(
                $eventName,
                function ($eventName, $payload) use ($eventType, $hookHandler) {
                    $this->handle($hookHandler, $eventType, $payload);
                }
            );
        }
    }

    protected function handle($hookHandler, $eventType, $payload)
    {
        if (is_string($hookHandler)) {
            $hookHandler = app($hookHandler);
        }

        /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $field */
        $field = $payload[0];

        $method = camel_case('resolved_'.$field->getHandle());

        if (method_exists($hookHandler, $method)) {
            $hookHandler->{$method}($field);
        }
    }
}
