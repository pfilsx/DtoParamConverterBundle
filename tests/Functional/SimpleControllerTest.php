<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Functional;

use Pfilsx\DtoParamConverter\Exception\ConverterValidationException;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Controller\SimpleController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SimpleControllerTest extends WebTestCase
{
    /**
     * @see SimpleController::getAction()
     */
    public function testGetAction(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/test', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => null,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::getActionWithPreloadDisabledInDto()
     */
    public function testGetActionWithPreloadDisabledInDto(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/test/disabled', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => null,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::getActionWithOverloadedSerializerContext()
     */
    public function testGetActionWithOverloadedSerializerContext(): void
    {
        self::expectException(ConverterValidationException::class);
        $client = self::createClient();
        $client->catchExceptions(false);
        $client->request(Request::METHOD_GET, '/test/strict', ['title' => 'Test title', 'value' => '20']);
    }

    /**
     * @see SimpleController::getWithPreloadAction()
     */
    public function testGetWithPreloadAction(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/test/1');

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test1',
            'value' => 10,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::getWithPreloadViaExpressionAction()
     */
    public function testGetWithPreloadViaExpressionAction(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/test/expression');

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test1',
            'value' => 10,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::postAction()
     */
    public function testPostAction(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_POST, '/test', ['title' => 'Test title', 'value' => 50]);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => 50,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::postAction()
     */
    public function testPostActionValidation(): void
    {
        self::expectException(ConverterValidationException::class);
        $client = self::createClient();
        $client->catchExceptions(false);
        $client->jsonRequest(Request::METHOD_POST, '/test', ['value' => 50]);
    }

    /**
     * @see SimpleController::postActionWithValidationDisabledInDto()
     */
    public function testPostActionWithValidationDisabledInDto(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_POST, '/test/disabled', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => null,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::postActionWithMultipleDto()
     */
    public function testPostActionWithMultipleDto(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_POST, '/test/multiple', ['url' => 'http://test.test']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'dto' => [
                'title' => 'Test1',
                'value' => 10,
            ],
            'dto2' => [
                'url' => 'http://test.test',
            ],
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::postActionWithMultipleDto()
     */
    public function testPostActionWithMultipleDtoOnInvalidData(): void
    {
        try {
            $client = self::createClient();
            $client->catchExceptions(false);
            $client->jsonRequest(Request::METHOD_POST, '/test/multiple', ['title' => '', 'url' => '']);
            self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        } catch (ConverterValidationException $exception) {
            $result = [];
            foreach ($exception->getViolations() as $violation) {
                $result[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            self::assertEquals([
                'title' => ['This value should not be blank.'],
                'url' => ['This value should not be blank.'],
            ], $result);
        }
    }

    /**
     * @see SimpleController::patchAction()
     */
    public function testPatchAction(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_PATCH, '/test', ['title' => 'Test title', 'value' => 50]);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => 50,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::patchWithPreloadAction()
     */
    public function testPatchWithPreloadAction(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_PATCH, '/test/1', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => 10,
        ], json_decode($client->getResponse()->getContent(), true));
    }
}
