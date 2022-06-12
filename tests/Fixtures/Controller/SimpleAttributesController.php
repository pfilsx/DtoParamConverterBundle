<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\Controller;

use Pfilsx\DtoParamConverter\Annotation\DtoResolver;
use Pfilsx\DtoParamConverter\Request\ArgumentResolver\DtoArgumentResolver;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Dto\TestAllDisabledAttributesDto;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Dto\TestAttributesDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class SimpleAttributesController extends AbstractController
{
    #[Route(path: "/attributes-test", methods: ["GET"])]
    #[DtoResolver(options: [DtoArgumentResolver::OPTION_PRELOAD_ENTITY => false])]
    public function getAction(TestAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    #[Route(path: "/attributes-test/disabled", methods: ["GET"])]
    public function getActionWithPreloadDisabledInDto(TestAllDisabledAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    #[Route(path: "/attributes-test/strict", methods: ["GET"])]
    #[DtoResolver(options: [DtoArgumentResolver::OPTION_PRELOAD_ENTITY => false, DtoArgumentResolver::OPTION_SERIALIZER_CONTEXT => ["disable_type_enforcement" => false]])]
    public function getActionWithOverloadedSerializerContext(TestAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    #[Route(path: "/attributes-test/{id}", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function getWithPreloadAction(TestAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    #[Route(path: "/attributes-test/expression", methods: ["GET"])]
    #[DtoResolver(options: [DtoArgumentResolver::OPTION_ENTITY_EXPR => "repository.find(1)"])]
    public function getWithPreloadViaExpressionAction(TestAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    #[Route(path: "/attributes-test", methods: ["POST"])]
    public function postAction(TestAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    #[Route(path: "/attributes-test/disabled", methods: ["POST"])]
    public function postActionWithValidationDisabledInDto(TestAllDisabledAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    #[Route(path: "/attributes-test", methods: ["PATCH"])]
    #[DtoResolver(options: [DtoArgumentResolver::OPTION_PRELOAD_ENTITY => false])]
    public function patchAction(TestAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    #[Route(path: "/attributes-test/{id}", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function patchWithPreloadAction(TestAttributesDto $dto): JsonResponse
    {
        return $this->json($dto);
    }
}
