<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Contract;


use Throwable;

interface NormalizerExceptionInterface
{
    public function __construct(string $message, int $code, Throwable $previous);
}