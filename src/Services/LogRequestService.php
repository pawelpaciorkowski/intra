<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\LogRequest;
use App\Services\Exception\LinkNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

final class LogRequestService
{
    private const array SKIP_ROUTES = [
        'login',
        'new-password',
        'password-change',
        'user-create',
        'user-update',
        'user-profile',
    ];

    private const array SKIP_URLS = [
    ];


    private ?LogRequest $logRequest = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LinkService $linkService
    ) {
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        return $this->entityManager->getRepository(LogRequest::class)->findAllByParams($parameterBag);
    }

    public function getLogRequest(): ?LogRequest
    {
        return $this->logRequest;
    }

    public function setLogRequest(LogRequest $logRequest): self
    {
        $this->logRequest = $logRequest;

        return $this;
    }

    public function saveRequest(Request $request): LogRequest
    {
        try {
            $link = $this->linkService->findOneByRoute($request->get('_route'));
        } catch (LinkNotFoundException) {
            $link = null;
        }

        $masked = false;
        foreach (self::SKIP_URLS as $url) {
            if (preg_match('/' . $url . '/i', $request->getRequestUri())) {
                $masked = true;

                break;
            }
        }

        if ($link && in_array($link->getName(), self::SKIP_ROUTES, true)) {
            $masked = true;
        }

        $content = $masked ? '[masked]' : $request->getContent();
        $requestBody = $masked ? '[masked]' : serialize($request->request->all());

        $logRequest = new LogRequest();
        $logRequest
            ->setUrl($request->getRequestUri())
            ->setIp($request->getClientIp())
            ->setMethod($request->getMethod())
            ->setContent($content)
            ->setRequest($requestBody)
            ->setLink($link);

        $this->entityManager->persist($logRequest);

        return $logRequest;
    }
}
