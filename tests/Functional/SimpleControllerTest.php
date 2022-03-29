<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Functional;

use Pfilsx\DtoParamConverter\Tests\Fixtures\Controller\SimpleController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class SimpleControllerTest extends WebTestCase
{
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
