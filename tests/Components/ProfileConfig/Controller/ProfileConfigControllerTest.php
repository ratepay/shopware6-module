<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\ProfileConfig\Controller;

use PHPUnit\Framework\TestCase;
use RatePAY\Exception\RequestException;
use Ratepay\RpayPayments\Components\ProfileConfig\Controller\ProfileConfigController;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigManagement;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProfileConfigControllerTest extends TestCase
{

    use IntegrationTestBehaviour;

    public function testSuccessful()
    {
        $controller = new ProfileConfigController($this->getManagementMock(true));
        $controller->setContainer($this->getContainer());
        $request = $this->createRequestObject(true);
        $response = $controller->reloadProfileConfiguration($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testFail()
    {
        $managementMock = $this->getManagementMock(false);

        $controller = new ProfileConfigController($managementMock);
        $controller->setContainer($this->getContainer());
        $request = $this->createRequestObject(true);
        $response = $controller->reloadProfileConfiguration($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(200, $response->getStatusCode()); // TODO maybe this should be not a 200-response
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('error', $data);
    }

    public function testExceptionHandling()
    {
        $managementMock = $this->getManagementMock(false, new RequestException('error message'));

        $controller = new ProfileConfigController($managementMock);
        $controller->setContainer($this->getContainer());
        $request = $this->createRequestObject(true);
        $response = $controller->reloadProfileConfiguration($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(500, $response->getStatusCode());
    }

    private function getManagementMock(bool $successFullResponse, \Exception $throwException = null): ProfileConfigManagement
    {
        $mock = $this->createMock(ProfileConfigManagement::class);
        if ($successFullResponse) {
            $profileEntity = $this->createProfileEntity();
        } else {
            $profileEntity = $this->createProfileEntity('error message');
        }

        $entityCollection = new EntityCollection([$profileEntity]);
        $searchResult = new EntitySearchResult(
            ProfileConfigEntity::class,
            $entityCollection->count(),
            $entityCollection,
            null,
            new Criteria(),
            Context::createDefaultContext()
        );
        $mock->method('refreshProfileConfigs')->willReturn($searchResult);

        if ($throwException) {
            $mock->method('refreshProfileConfigs')->willThrowException($throwException);
        }

        return $mock;
    }

    private function createProfileEntity(string $statusMessage = null): ProfileConfigEntity
    {
        $entity = new ProfileConfigEntity();
        $entity->setUniqueIdentifier(123456);
        $entity->setId($entity->getUniqueIdentifier());

        $ref = new \ReflectionClass($entity);
        $this->setObjectValueByReflection($ref, $entity, ProfileConfigEntity::FIELD_PROFILE_ID, '1212345678');
        $this->setObjectValueByReflection($ref, $entity, ProfileConfigEntity::FIELD_STATUS_MESSAGE, $statusMessage);
        $this->setObjectValueByReflection($ref, $entity, ProfileConfigEntity::FIELD_STATUS, !$statusMessage);
        return $entity;
    }

    private function setObjectValueByReflection(\ReflectionClass $refClass, $object, string $property, $value)
    {
        $refProperty = $refClass->getProperty($property);
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $value);
    }

    private function createRequestObject($hasId): Request
    {
        $query = $hasId ? ['id' => 123] : [];

        return (new Request([], $query));
    }
}
