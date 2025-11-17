<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

#[AsEventListener(RequestEvent::class, 'onRequest')]
#[AsEventListener(ResponseEvent::class, 'onResponse')]
final readonly class RateLimiterEventListener
{
    public function __construct(private RateLimiterFactoryInterface $eventCreationLimiter)
    {
    }

    public function onRequest(RequestEvent $event): void
    {
        $limiter = $this->eventCreationLimiter->create($event->getRequest()->getClientIp());
        if (false === $limiter->consume()->isAccepted()) {
            $event->setResponse(
                new Response(status: Response::HTTP_TOO_MANY_REQUESTS)
            );
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        $limiter = $this->eventCreationLimiter->create($event->getRequest()->getClientIp());

        $limit = $limiter->consume(match ($event->getResponse()->getStatusCode()) {
            Response::HTTP_NOT_FOUND => 5,
            Response::HTTP_FORBIDDEN, Response::HTTP_METHOD_NOT_ALLOWED, Response::HTTP_BAD_REQUEST => 10,
            Response::HTTP_UNAUTHORIZED => 100,
            default => 0,
        });
    }
}
