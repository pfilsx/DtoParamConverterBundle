<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\Controller;

use Pfilsx\DtoParamConverter\Request\ParamConverter\DtoParamConverter;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Dto\TestDto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class SimpleController extends AbstractController
{
    /**
     * @Route("/test/{id}", methods={"GET"})
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
     * @Route("/test", methods={"PATCH"})
     * @ParamConverter("dto", options={
     *     DtoParamConverter::OPTION_PRELOAD_ENTITY: false
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
     * @Route("/test/{id}", methods={"PATCH"})
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
