<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Configuration;

/**
 * @internal
 */
final class Configuration
{
    private PreloadConfiguration $preloadConfiguration;

    private SerializerConfiguration $serializerConfiguration;

    private ValidationConfiguration $validationConfiguration;

    public function __construct(
        array $preloadParams,
        array $serializerParams,
        array $validationParams
    ) {
        $this->preloadConfiguration = PreloadConfiguration::create($preloadParams);
        $this->serializerConfiguration = SerializerConfiguration::create($serializerParams);
        $this->validationConfiguration = ValidationConfiguration::create($validationParams);
    }

    public function getPreloadConfiguration(): PreloadConfiguration
    {
        return $this->preloadConfiguration;
    }

    public function getSerializerConfiguration(): SerializerConfiguration
    {
        return $this->serializerConfiguration;
    }

    public function getValidationConfiguration(): ValidationConfiguration
    {
        return $this->validationConfiguration;
    }
}
