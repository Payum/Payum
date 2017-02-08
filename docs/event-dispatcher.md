# Event Dispatcher

The EventDispatcherExtensions provides a Bridge to the [Symfony EventDispatcher Component](http://symfony.com/doc/current/components/event_dispatcher/index.html). The EventDispatcherComponent allows you to add behaviour without changing Payum.

## Enable the EventDispatcherExtension

```php
<?php

use Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension;

/** @var \Payum\Core\Gateway $gateway */
/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */

$gateway->addExtension(
    new EventDispatcherExtension($eventDispatcher)
);
```

## Listen to an Event

```php
<?php

use Payum\Core\Bridge\Symfony\Event\ExecuteEvent;
use Payum\Core\Bridge\Symfony\PayumEvents;

/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */

$eventDispatcher->addListener(
    PayumEvents::GATEWAY_EXECUTE,
    function(ExecuteEvent $event) {
        // do something
    }
);
```

| Name | `PayumEvents` Constant | Argument passed to the listener |
| --- | --- | ---|
| payum.gateway.pre_execute | `PayumEvents::GATEWAY_PRE_EXECUTE` | `ExecuteEvent` |
| payum.gateway.execute | `PayumEvents::GATEWAY_EXECUTE` | `ExecuteEvent` |
| payum.gateway.post_execute | `PayumEvents::GATEWAY_POST_EXECUTE` | `ExecuteEvent` |

## Benefit with PayumBundle

If you use Symfony Full-Stack Framework and the PayumBundle you can add the EventDispatcherExtension via Configuration:

```yaml
services:
    app.payum.extension.event_dispatcher:
        class: Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension
        arguments: ["@event_dispatcher"]
        tags:
            - { name: payum.extension, all: true, prepend: false }
```

And add the listener:

```yaml
services:
    app.payum.listener.render_template:
        class: AppBundle\EventListener\RenderTemplateListener
        tags:
            - { name: kernel.event_listener, event: payum.gateway.execute }
```

Back to [index](index.md).
