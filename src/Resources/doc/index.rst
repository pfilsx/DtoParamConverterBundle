Getting started
===============

Prerequisites
-------------

This bundle requires Symfony 4.4+ and PHP 7.4+

Installation
------------

Open a command console, enter your project directory and execute the following command to download the latest version of this bundle:

.. code-block:: terminal

    $ composer require pfilsx/dto-param-converter-bundle

Or just add the following lines in your ``composer.json`` require section manually:

.. code-block:: json

    {
        "require": {
            "pfilsx/dto-param-converter-bundle": "^2.0"
        }
    }

and run ``composer update`` or ``composer update pfilsx/dto-param-converter-bundle``.

Register the bundle
~~~~~~~~~~~~~~~~~~~

Register bundle into ``config/bundles.php`` (Flex did it automatically):

.. code-block:: php

   return [
       //...
       Pfilsx\DtoParamConverter\DtoParamConverterBundle::class => ['all' => true],
   ];

Configuration
-------------

You can configure the bundle as you need in configuration file:

.. code-block:: yaml

    # config/packages/dto_param_converter.yaml
    dto_param_converter:
        preload:
            enabled: true
            methods: ['GET', 'PATCH']
            optional: false
            entity_manager_name: null
        serializer:
            service: serializer
            normalizer_exception_class: 'Pfilsx\DtoParamConverter\Exception\NotNormalizableConverterValueException'
            strict_types:
                enabled: true
                excluded_methods: ['GET']
        validation:
            enabled: true
            excluded_methods: ['GET']
            exception_class: 'Pfilsx\DtoParamConverter\Exception\ConverterValidationException'

Global configuration can be overloaded via Dto or DtoResolver annotation/attribute.
For more information about global configuration see `Configuration reference <1-configuration-reference.rst>`_.

Usage
-----

1. Create DTO
~~~~~~~~~~~~~

Create DTO class with converter annotation/attribute

.. code-block:: php

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

      // ...
    }

For more information about Dto annotation/attribute see `Dto annotation <2-dto-annotation.rst>`_.

2. Use created DTO as parameter in your controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    class Controller
    {
        // ...
        public function postAction(SomeDto $dto): Response
        {
            // here you can work with already prepared DTO
        }
    }

Also you can overwrite global bundle configuration via DtoResolver annotation/attribute:

.. code-block:: php

    class Controller
    {
        /*
        * ...
        * @DtoResolver(options={
        *     DtoArgumentResolver::OPTION_VALIDATE: false
        * })
        */
        public function postAction(SomeDto $dto): Response
        {
            // ...
        }
    }

For more information about DtoResolver annotation/attribute see `DtoResolver annotation <3-dto-resolver-annotation.rst>`_.

3. Entity preload
~~~~~~~~~~~~~~~~~

If you need to preload some data from entity to DTO before request mapping you should:

1. Enable preload in configuration

2. Link DTO with entity

.. code-block:: php

    /**
    * @Dto(linkedEntity=SomeEntity::class)
    */
    final class SomeDto
    {
      // ...
    }

3. Create a DTO mapper

.. code-block:: php

    use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;

    final class SomeDtoMapper implements DtoMapperInterface
    {
        public static function getDtoClassName(): string
        {
            return SomeDto::class;
        }

        /**
         * @param SomeEntity   $entity
         * @param SomeDto $dto
         */
        public function mapToDto($entity, $dto): void
        {
            // your entity to dto mapping logic
            $dto->title = $entity->getTitle();
            // ...
        }
    }