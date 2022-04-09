DTO Param Converter Bundle
==============

[![PHP Version Require](http://poser.pugx.org/pfilsx/dto-param-converter-bundle/require/php)](https://packagist.org/packages/pfilsx/dto-param-converter-bundle)
[![Latest Stable Version](http://poser.pugx.org/pfilsx/dto-param-converter-bundle/v)](https://packagist.org/packages/pfilsx/dto-param-converter-bundle)  
[![Tests](https://github.com/pfilsx/DtoParamConverterBundle/actions/workflows/tests.yaml/badge.svg?branch=master)](https://github.com/pfilsx/DtoParamConverterBundle/actions/workflows/tests.yaml)
[![Total Downloads](http://poser.pugx.org/pfilsx/dto-param-converter-bundle/downloads)](https://packagist.org/packages/pfilsx/dto-param-converter-bundle)

Introduction
------------

The bundle provides a simple way to convert requests into DTO, validate and map to entity in Your Symfony REST API Project. It automatically deserealize request content into provided dto, validates it (if required) and injects dto into your controller argument and finally you have a fully valid dto in your controller.

Features
--------
* Request deserialization into dto 
* Automatic validation if validator is included in project and dto has asserts annotations
* Easy to configure converter options for each request via annotations(serializer, validator options etc)
* Entity preload into dto before request deserialization(on patch/get methods or if forced in configuration)
* Link dto with entity via annotation and simple mapper

Requirement
-----------
* PHP 7.4+
* Symfony 4.4+|5.3+|6.0+
* SensioFrameworkExtraBundle

Installation
------------

Via bash:
```bash
$ composer require pfilsx/dto-param-converter-bundle
```
Via composer.json:

You need to add the following lines in your deps :
```json
{
    "require": {
        "pfilsx/dto-param-converter-bundle": "^1.0"
    }
}
```

For non symfony-flex apps dont forget to add bundle:
``` php
$bundles = array(
    ...
    new Pfilsx\DtoParamConverter\DtoParamConverterBundle(),
);
```

Usage
-----
1. Create DTO class with converter annotation
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

    public function mapToEntity(object $dto, object $entity): void
    {
        // your dto to entity mapping logic
        $entity->setTitle($dto->title);
        ...
    }
}
```
5. You can use your dto mapper manually
```php

private DtoMapperFactory $mapperFactory;

public function __construct(DtoMapperFactory $mapperFactory)
{
    $this->mapperFactory = $mapperFactory;
}

public function someMethod(SomeDto $dto): void 
{
    $entity = new SomeEntity();
    $mapper = $this->mapperFactory->getMapper(SomeDto::class);
    $mapper->mapToEntity($dto, $entity);
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
* @ParamConverter("someDto", options={
*    DtoParamConverter::OPTION_SERIALIZER_CONTEXT: {},
*    DtoParamConverter::OPTION_VALIDATOR_GROUPS: {},
*    DtoParamConverter::OPTION_PRELOAD_ENTITY: true,
*    DtoParamConverter::OPTION_STRICT_PRELOAD_ENTITY: true,
*    DtoParamConverter::OPTION_ENTITY_ID_ATTRIBUTE: null,
*    DtoParamConverter::OPTION_ENTITY_MANAGER: null,
*    DtoParamConverter::OPTION_ENTITY_MAPPING: {}
*    DtoParamConverter::OPTION_ENTITY_EXPR: null,
*    DtoParamConverter::OPTION_VALIDATE: false
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