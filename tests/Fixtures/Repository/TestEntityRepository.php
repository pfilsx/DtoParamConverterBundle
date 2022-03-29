<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\Repository;

use Doctrine\Persistence\ObjectRepository;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Entity\TestEntity;

final class TestEntityRepository implements ObjectRepository
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

    public function find($id): ?TestEntity
    {
        return $this->storage[$id] ?? null;
    }

    public function findOneBy(array $criteria): ?TestEntity
    {
        $filteredStorage = $this->findBy($criteria);

        return array_shift($filteredStorage);
    }

    public function findAll(): array
    {
        return $this->storage;
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return array_filter($this->storage, static function ($entity) use ($criteria) {
            foreach ($criteria as $key => $value) {
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
    }

    public function getClassName(): string
    {
        return TestEntity::class;
    }
}
