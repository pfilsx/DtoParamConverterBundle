<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Contract;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ValidationExceptionInterface
{
    public function getViolations(): ConstraintViolationListInterface;

    public function setViolations(ConstraintViolationListInterface $violations): self;
}
