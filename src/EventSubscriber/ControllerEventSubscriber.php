<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\EventSubscriber;

use Pfilsx\DtoParamConverter\Provider\RouteMetadataProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ControllerEventSubscriber implements EventSubscriberInterface
{
    private RouteMetadataProvider $routeMetadataProvider;

    public function __construct(RouteMetadataProvider $routeMetadataProvider)
    {
        $this->routeMetadataProvider = $routeMetadataProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(KernelEvent $event): void
    {
        $controller = $event->getController();

        if (!\is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!\is_array($controller)) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');

        if (empty($route)) {
            return;
        }

        $this->routeMetadataProvider->createMetadata($route, $controller);
    }
}
