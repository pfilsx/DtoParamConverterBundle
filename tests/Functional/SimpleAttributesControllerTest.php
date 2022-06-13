<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Functional;

use Pfilsx\DtoParamConverter\Exception\ConverterValidationException;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Controller\SimpleAttributesController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @requires PHP >= 8.0
 */
final class SimpleAttributesControllerTest extends WebTestCase
{
    /**
     * @see SimpleAttributesController::getAction()
     */
    public function testGetAction(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/attributes-test', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => null,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleAttributesController::getActionWithPreloadDisabledInDto()
     */
    public function testGetActionWithPreloadDisabledInDto(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/attributes-test/disabled', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => null,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleAttributesController::getActionWithOverloadedSerializerContext()
     */
    public function testGetActionWithOverloadedSerializerContext(): void
    {
        self::expectException(ConverterValidationException::class);
        $client = self::createClient();
        $client->catchExceptions(false);
        $client->request(Request::METHOD_GET, '/attributes-test/strict', ['title' => 'Test title', 'value' => '20']);
    }

    /**
     * @see SimpleAttributesController::getWithPreloadAction()
     */
    public function testGetWithPreloadAction(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/attributes-test/1');

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test1',
            'value' => 10,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleAttributesController::getWithPreloadViaExpressionAction()
     */
    public function testGetWithPreloadViaExpressionAction(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/attributes-test/expression');

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test1',
            'value' => 10,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleAttributesController::postAction()
     */
    public function testPostAction(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_POST, '/attributes-test', ['title' => 'Test title', 'value' => 50]);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => 50,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleAttributesController::postAction()
     */
    public function testPostActionValidation(): void
    {
        self::expectException(ConverterValidationException::class);
        $client = self::createClient();
        $client->catchExceptions(false);
        $client->jsonRequest(Request::METHOD_POST, '/attributes-test', ['value' => 50]);
    }

    /**
     * @see SimpleAttributesController::postActionWithValidationDisabledInDto()
     */
    public function testPostActionWithValidationDisabledInDto(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_POST, '/attributes-test/disabled', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => null,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleAttributesController::patchAction()
     */
    public function testPatchAction(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_PATCH, '/attributes-test', ['title' => 'Test title', 'value' => 50]);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => 50,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @see SimpleAttributesController::patchWithPreloadAction()
     */
    public function testPatchWithPreloadAction(): void
    {
        $client = self::createClient();
        $client->jsonRequest(Request::METHOD_PATCH, '/attributes-test/1', ['title' => 'Test title']);

        $this->assertResponseIsSuccessful();
        self::assertEquals([
            'title' => 'Test title',
            'value' => 10,
        ], json_decode($client->getResponse()->getContent(), true));
    }
}
