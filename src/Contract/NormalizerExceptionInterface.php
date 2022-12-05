<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Contract;

interface NormalizerExceptionInterface
{
    public function __construct(string $message, int $code, \Throwable $previous);
}
