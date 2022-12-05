<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Collector;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationCollector
{
    private ConstraintViolationList $violations;

    public function __construct()
    {
        $this->violations = new ConstraintViolationList();
    }

    public function addViolation(ConstraintViolationInterface $violation): self
    {
        $this->violations->add($violation);

        return $this;
    }

    public function addAllViolations(ConstraintViolationListInterface $violations): self
    {
        $this->violations->addAll($violations);

        return $this;
    }

    public function hasViolations(): bool
    {
        return $this->violations->count() > 0;
    }

    public function getViolations(): ConstraintViolationList
    {
        return $this->violations;
    }
}
