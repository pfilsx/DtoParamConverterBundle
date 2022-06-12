DTO Param Converter Bundle
==============

[![PHP Version Require](http://poser.pugx.org/pfilsx/dto-param-converter-bundle/require/php)](https://packagist.org/packages/pfilsx/dto-param-converter-bundle)
[![Latest Stable Version](http://poser.pugx.org/pfilsx/dto-param-converter-bundle/v)](https://packagist.org/packages/pfilsx/dto-param-converter-bundle)  
[![Tests](https://github.com/pfilsx/DtoParamConverterBundle/actions/workflows/tests.yaml/badge.svg?branch=master)](https://github.com/pfilsx/DtoParamConverterBundle/actions/workflows/tests.yaml)
[![Total Downloads](http://poser.pugx.org/pfilsx/dto-param-converter-bundle/downloads)](https://packagist.org/packages/pfilsx/dto-param-converter-bundle)

Description
------------

The bundle provides a simple way to map requests into DTO(Data Transfer Object), 
validate and inject into Your Symfony project controller. 
It automatically deserealize request content into provided DTO, 
validates it (if required) and injects DTO into your controller 
argument([Symfony Argument Resolver](https://symfony.com/doc/current/controller/argument_value_resolver.html)), 
and finally you have a fully valid DTO in your controller.

Features
--------
* Request deserialization into dto with configurable serializer
* Automatic configurable validation using [Symfony validator](https://symfony.com/doc/current/validation.html)
* Easy to configure converter options for each request/DTO via annotations/PHP8 attributes(preload, serializer, validator options etc)
* Entity preload into DTO before request deserialization

Requirement
-----------
* PHP 7.4+|8.x
* Symfony 4.4+|5.3+|6.0+

Installation
------------

Open a command console, enter your project directory and execute the following command to download the latest version of this bundle:
```bash
composer require pfilsx/dto-param-converter-bundle
```

Register bundle into ``config/bundles.php`` (Flex did it automatically):
``` php
return [
    ...
    Pfilsx\DtoParamConverter\DtoParamConverterBundle::class => ['all' => true],
];
```

Documentation
-------------

Documentation can be found [here](src/Resources/doc/index.rst).

Usage
-----

1. Create DTO class with converter annotation/attribute
```php
use Pfilsx\DtoParamConverter\Annotation\Dto;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @Dto() 
*/
final class SomeDto 
{
  /**
  * @Assert\NotBlank
  */
  public ?string $title = null;
  
  ...
}
```

2. Use DTO in your controller
```php
public function postAction(SomeDto $someDto): Response
{
    // here dto already loaded and validated
}
```

3. Link DTO with entity(if preload required)
```php
/**
* @Dto(linkedEntity=SomeEntity::class) 
*/
final class SomeDto 
{
    ...
}
```

4. Create entity-dto mapper(if preload required)
```php

use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;

final class SomeDtoMapper implements DtoMapperInterface
{
    public static function getDtoClassName(): string
    {
        return SomeDto::class;
    }

    /**
     * @param object|SomeEntity   $entity
     * @param SomeDto|object $dto
     */
    public function mapToDto(object $entity, object $dto): void
    {
        // your entity to dto mapping logic
        $dto->title = $entity->getTitle();
        ...
    }
}
```

Configuration
-------------

You can configure bundle globally via `config/packages/dto_param_converter.yaml`:

```yaml
dto_param_converter:
  preload: # entity preload into dto configuration
    enabled: true # enable/disable entity preloading before request mapping
    methods: ['GET', 'PATCH'] # request methods that require the entity preload
    optional: false # if false the converter will throw NotFoundHttpException on entity for preloading not found otherwise it will ignore preloading
    entity_manager_name: null # entity manager name to use for entity preloading. useful on multiple managers
  serializer: # request deserialization configuration 
    service: serializer # serializer should be used for request deserialization
    normalizer_exception_class: 'Pfilsx\DtoParamConverter\Exception\NotNormalizableConverterValueException' # exception class that should be thrown on normalization errors. not actual after 5.4 symfony/serializer
    strict_types: # types enforcement on denormalization
      enabled: true
      excluded_methods: ['GET'] # excluded request methods for types enforcement
  validation: # dto validation configuration
    enabled: true # enable/disable validation of dto
    excluded_methods: ['GET'] # excluded request methods for validation
    exception_class: 'Pfilsx\DtoParamConverter\Exception\ConverterValidationException' # exception class that should be thrown on validation errors
```

Or You can configure converter for each action

```php
/**
* @DtoResolver(options={
*    DtoArgumentResolver::OPTION_SERIALIZER_CONTEXT: {},
*    DtoArgumentResolver::OPTION_VALIDATOR_GROUPS: {},
*    DtoArgumentResolver::OPTION_PRELOAD_ENTITY: true,
*    DtoArgumentResolver::OPTION_STRICT_PRELOAD_ENTITY: true,
*    DtoArgumentResolver::OPTION_ENTITY_ID_ATTRIBUTE: null,
*    DtoArgumentResolver::OPTION_ENTITY_MANAGER: null,
*    DtoArgumentResolver::OPTION_ENTITY_MAPPING: {}
*    DtoArgumentResolver::OPTION_ENTITY_EXPR: null,
*    DtoArgumentResolver::OPTION_VALIDATE: false
* })
*/
public function someAction(SomeDto $someDto): Response
{
    ...
}
```

License
-------

This bundle is released under the MIT license.

Contribute
----------

If you'd like to contribute, feel free to propose a pull request, create issue or just contact me :) 