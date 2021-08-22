<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Exception;


use Pfilsx\DtoParamConverter\Contract\ValidationExceptionInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ConverterValidationException extends DtoConverterException implements ValidationExceptionInterface
{
    private ConstraintViolationListInterface $violations;

    public function __construct()
    {
        parent::__construct('Bad Request', 400);
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function setViolations(ConstraintViolationListInterface $violations): self
    {
        $this->violations = $violations;
        return $this;
    }
}