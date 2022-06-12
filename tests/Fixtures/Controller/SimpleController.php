<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\Controller;

use Pfilsx\DtoParamConverter\Annotation\DtoResolver;
use Pfilsx\DtoParamConverter\Request\ArgumentResolver\DtoArgumentResolver;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Dto\TestAllDisabledDto;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Dto\TestDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class SimpleController extends AbstractController
{
    /**
     * @Route("/test", methods={"GET"})
     *
     * @DtoResolver({
     *     DtoArgumentResolver::OPTION_PRELOAD_ENTITY: false
     * })
     *
     * @param TestDto $dto
     *
     * @return JsonResponse
     */
    public function getAction(TestDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    /**
     * @Route("/test/disabled", methods={"GET"})
     *
     * @param TestAllDisabledDto $dto
     *
     * @return JsonResponse
     */
    public function getActionWithPreloadDisabledInDto(TestAllDisabledDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    /**
     * @Route("/test/strict", methods={"GET"})
     *
     * @DtoResolver({
     *     DtoArgumentResolver::OPTION_PRELOAD_ENTITY: false,
     *     DtoArgumentResolver::OPTION_SERIALIZER_CONTEXT: {"disable_type_enforcement": false}
     * })
     *
     * @param TestDto $dto
     *
     * @return JsonResponse
     */
    public function getActionWithOverloadedSerializerContext(TestDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    /**
     * @Route("/test/{id}", methods={"GET"}, requirements={"id": "\d+"})
     *
     * @param TestDto $dto
     *
     * @return JsonResponse
     */
    public function getWithPreloadAction(TestDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    /**
     * @Route("/test/expression", methods={"GET"})
     *
     * @DtoResolver({
     *     DtoArgumentResolver::OPTION_ENTITY_EXPR: "repository.find(1)"
     * })
     *
     * @param TestDto $dto
     *
     * @return JsonResponse
     */
    public function getWithPreloadViaExpressionAction(TestDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    /**
     * @Route("/test", methods={"POST"})
     *
     * @param TestDto $dto
     *
     * @return JsonResponse
     */
    public function postAction(TestDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    /**
     * @Route("/test/disabled", methods={"POST"})
     *
     * @param TestAllDisabledDto $dto
     *
     * @return JsonResponse
     */
    public function postActionWithValidationDisabledInDto(TestAllDisabledDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    /**
     * @Route("/test", methods={"PATCH"})
     * @DtoResolver({
     *     DtoArgumentResolver::OPTION_PRELOAD_ENTITY: false
     * })
     *
     * @param TestDto $dto
     *
     * @return JsonResponse
     */
    public function patchAction(TestDto $dto): JsonResponse
    {
        return $this->json($dto);
    }

    /**
     * @Route("/test/{id}", methods={"PATCH"}, requirements={"id": "\d+"})
     *
     * @param TestDto $dto
     *
     * @return JsonResponse
     */
    public function patchWithPreloadAction(TestDto $dto): JsonResponse
    {
        return $this->json($dto);
    }
}
