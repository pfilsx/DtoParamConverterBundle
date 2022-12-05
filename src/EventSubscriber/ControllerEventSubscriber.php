<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\EventSubscriber;

use Pfilsx\DtoParamConverter\Collector\ValidationCollector;
use Pfilsx\DtoParamConverter\Configuration\Configuration;
use Pfilsx\DtoParamConverter\Provider\RouteMetadataProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ControllerEventSubscriber implements EventSubscriberInterface
{
    private Configuration $configuration;

    private RouteMetadataProvider $routeMetadataProvider;

    private ValidationCollector $validationCollector;

    public function __construct(
        Configuration $configuration,
        RouteMetadataProvider $routeMetadataProvider,
        ValidationCollector $validationCollector
    ) {
        $this->configuration = $configuration;
        $this->routeMetadataProvider = $routeMetadataProvider;
        $this->validationCollector = $validationCollector;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments',
        ];
    }

    public function onControllerArguments(ControllerArgumentsEvent $event): void
    {
        if (!$this->validationCollector->hasViolations()) {
            return;
        }

        $exceptionClass = $this->configuration->getValidationConfiguration()->getExceptionClass();
        $exception = new $exceptionClass();
        $exception->setViolations($this->validationCollector->getViolations());

        throw $exception;
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
