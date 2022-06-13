DtoResolver annotation
~~~~~~~~~~~~~~~~~~~~~~

This annotation allows your to configure converter logic for specific route. Bundle supports annotations or PHP8 attributes style.

Annotation example:
...................

.. code-block:: php

    /**
    * @Route(...)
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
    public function someAction()
    {
        // ...
    }

Attribute example:
..................

.. code-block:: php

    #[Route(...)]
    #[DtoResolver(options: [
        DtoArgumentResolver::OPTION_SERIALIZER_CONTEXT => [],
        DtoArgumentResolver::OPTION_VALIDATOR_GROUPS => [],
        DtoArgumentResolver::OPTION_PRELOAD_ENTITY => true,
        DtoArgumentResolver::OPTION_STRICT_PRELOAD_ENTITY => true,
        DtoArgumentResolver::OPTION_ENTITY_ID_ATTRIBUTE => 'id',
        DtoArgumentResolver::OPTION_ENTITY_MANAGER => 'my_manager',
        DtoArgumentResolver::OPTION_ENTITY_MAPPING => []
        DtoArgumentResolver::OPTION_ENTITY_EXPR => 'repository.find(id)',
        DtoArgumentResolver::OPTION_VALIDATE => false
    ])]
    public function someAction()
    {
        // ...
    }

Reference:
..........

- **DtoArgumentResolver::OPTION_SERIALIZER_CONTEXT** - serializer context to be used on request deserialization.
- **DtoArgumentResolver::OPTION_VALIDATOR_GROUPS** - validation groups to be used on DTO validation.
- **DtoArgumentResolver::OPTION_PRELOAD_ENTITY** - enable/disable entity preload. Overload global and DTO configuration if not null.
- **DtoArgumentResolver::OPTION_STRICT_PRELOAD_ENTITY** - enable/disable strict preloading. Overload global configuration if not null.
- **DtoArgumentResolver::OPTION_ENTITY_ID_ATTRIBUTE** - specify attribute key from route should be used for entity find by primary key in preload process.
- **DtoArgumentResolver::OPTION_ENTITY_MANAGER** - specify entity manager name should be used for entity find in preload process. Overload global configuration if not null.
- **DtoArgumentResolver::OPTION_ENTITY_MAPPING** - configures the properties and values to use with the findOneBy() method in preload process: the key is the route placeholder name and the value is the Doctrine property name.
- **DtoArgumentResolver::OPTION_ENTITY_EXPR** - expression to fetch the entity by calling a method on your repository. The ``repository`` method will be your entity's Repository class and any route wildcards - like {id} are available as variables.
- **DtoArgumentResolver::OPTION_VALIDATE** - enable/disable DTO validation by converter. Overload global and DTO configuration if not null.
