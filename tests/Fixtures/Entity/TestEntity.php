<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Repository\TestEntityRepository;

/**
 * @Mapping\Entity(repositoryClass=TestEntityRepository::class)
 */
class TestEntity
{
    /**
     * @Mapping\Column(type="integer")
     * @Mapping\Id
     * @Mapping\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @Mapping\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @Mapping\Column(type="integer")
     */
    private int $value;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }
}
