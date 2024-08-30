<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class LocaleListener
{
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 32)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = $request->getPreferredLanguage(['fr', 'en', 'de', 'es']);

        if ($locale) {
            $request->setDefaultLocale($locale);
        }
    }
}
