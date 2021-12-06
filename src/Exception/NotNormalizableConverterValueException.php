<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Exception;

use Pfilsx\DtoParamConverter\Contract\NormalizerExceptionInterface;

final class NotNormalizableConverterValueException extends DtoConverterException implements NormalizerExceptionInterface
{
}
