<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\Repository;

use Pfilsx\DtoParamConverter\Tests\Fixtures\Entity\TestEntity;

final class TestEntityRepository
{
    /**
     * @var TestEntity[]
     */
    private array $storage = [];

    public function __construct()
    {
        $entity1 = new TestEntity();
        $entity1
            ->setId(1)
            ->setTitle('Test1')
            ->setValue(10);

        $this->storage[1] = $entity1;

        $entity2 = new TestEntity();
        $entity2
            ->setId(2)
            ->setTitle('Test2')
            ->setValue(20);

        $this->storage[2] = $entity2;
    }

    public function find(int $id): ?TestEntity
    {
        return $this->storage[$id] ?? null;
    }

    public function findOneBy(array $params): ?TestEntity
    {
        $filteredStorage = array_filter($this->storage, static function ($entity) use ($params) {
            foreach ($params as $key => $value) {
                $getter = 'get' . ucfirst($key);

                if (!\method_exists($entity, $getter)) {
                    return false;
                }

                if ($entity->$getter() !== $value) {
                    return false;
                }
            }

            return true;
        });

        return array_shift($filteredStorage);
    }
}
