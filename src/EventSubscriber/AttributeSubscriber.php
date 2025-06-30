<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Attribute\Breadcrumb;
use App\Services\BreadcrumbContainerService;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class AttributeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly BreadcrumbContainerService $breadcrumbContainerService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(ControllerEvent $event): void
    {
        // Only master request
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (!is_array($controller = $event->getController())) {
            return;
        }


        $class = new ReflectionClass($controller[0]);

        foreach ($class->getAttributes() as $attribute) {
            if ($attribute->getName() === Breadcrumb::class) {
                $this->breadcrumbContainerService->add($attribute->getArguments()[0]);
            }
        }

        $method = new ReflectionMethod($controller[0], $controller[1]);

        foreach ($method->getAttributes() as $attribute) {
            if ($attribute->getName() === Breadcrumb::class) {
                $this->breadcrumbContainerService->add($attribute->getArguments()[0]);
            }
        }
    }
}
