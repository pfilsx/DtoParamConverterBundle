<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Exception;


use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ConverterValidationException extends Exception
{
    private ConstraintViolationListInterface $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        $this->violations = $violations;
        parent::__construct('', 400);
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}