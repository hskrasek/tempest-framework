<?php

namespace Tempest\Events;

use ReflectionMethod;
use Tempest\Commands\Middleware;

final class EventBusConfig
{
    public function __construct(
        /** @var \Tempest\Events\EventHandler[][] */
        public array $handlers = [],

        /** @var \Tempest\Commands\Middleware[] */
        public array $middleware = [],
    ) {
    }

    public function addHandler(EventHandler $eventHandler, string $eventName, ReflectionMethod $reflectionMethod): self
    {
        $this->handlers[$eventName][] = $eventHandler
            ->setEventName($eventName)
            ->setHandler($reflectionMethod);

        return $this;
    }

    public function addMiddleware(Middleware $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }
}