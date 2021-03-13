DTO Param Converter Bundle
==============

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
* Symfony 4.4+|5.1+
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
        // your mapping logic
    }

    public function mapToEntity(object $dto, object $entity): void
    {
        // your mapping logic
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
```php
/**
* @ParamConverter("someDto", options={
*    DtoParamConverter::OPTION_SERIALIZER_CONTEXT: {},
*    DtoParamConverter::OPTION_VALIDATOR_GROUPS: [],
*    DtoParamConverter::OPTION_PRELOAD_ENTITY: true,
*    DtoParamConverter::OPTION_STRICT_PRELOAD_ENTITY: true,
*    DtoParamConverter::OPTION_ENTITY_ATTRIBUTE: null,
*    DtoParamConverter::OPTION_ENTITY_MANAGER: null,
*    DtoParamConverter::OPTION_ENTITY_MAPPING: []
*    DtoParamConverter::OPTION_ENTITY_EXPR: null,
*    DtoParamConverter::OPTION_FORCE_VALIDATE: false
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

If you'd like to contribute, feel free to propose a pull request! Or just contact me :) 