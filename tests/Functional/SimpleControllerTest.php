<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Functional;

use Pfilsx\DtoParamConverter\Exception\ConverterValidationException;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Controller\SimpleController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

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
        ], \json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleController::getActionWithPreloadDisabledByAnnotation()
     */
    public function testGetActionWithPreloadDisabledByAnnotation(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/test/disabled', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => null,
        ], \json_decode($client->getResponse()->getContent(), true));
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
        ], \json_decode($client->getResponse()->getContent(), true));
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
        ], \json_decode($client->getResponse()->getContent(), true));
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
        ], \json_decode($client->getResponse()->getContent(), true));
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
        ], \json_decode($client->getResponse()->getContent(), true));
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
        ], \json_decode($client->getResponse()->getContent(), true));
    }
}
