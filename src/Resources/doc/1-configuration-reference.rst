Configuration reference
=======================

Example:
~~~~~~~~

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

preload
.......

Entity preload into DTO configuration

- **enabled** (default: true) - enable/disable entity preloading before request mapping
- **methods** (default: ['GET', 'PATCH']) - request methods that require the entity preload
- **optional** (default: false) - if false the converter will throw NotFoundHttpException on entity for preloading not found otherwise it will ignore preloading
- **entity_manager_name** (default: null) - specified entity manager name to use for entity preloading. useful on multiple managers

serializer
..........

Request deserialization configuration

- **service** (default: 'serializer') - serializer service name should be used for request deserialization
- **normalizer_exception_class** (default: 'Pfilsx\DtoParamConverter\Exception\NotNormalizableConverterValueException') - exception class that should be thrown on normalization errors. obsolete after 5.4 symfony/serializer

serializer.strict_types
.......................

Types enforcement on denormalization process

- **enabled** (default: true) - enable/disable types enforcement
- **excluded_methods** (default: ['GET']) - excluded request methods for types enforcement

validation
..........

Dto validation configuration

- **enabled** (default: true) - enable/disable validation of dto
- **excluded_methods** (default: ['GET']) - excluded request methods for validation
- **exception_class** (default: 'Pfilsx\DtoParamConverter\Exception\ConverterValidationException') - exception class that should be thrown on validation errors
