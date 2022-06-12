DTO annotation
==============

This annotation allows your to configure converter logic for specific DTO. Bundle supports annotations or PHP8 attributes style.

Annotation example:
...................

.. code-block:: php

    /**
    * @Dto(linkedEntity=SomeEntity::class, preload=false, validate=false)
    */
    class SomeDto
    {
        // ...
    }

Attribute example:
..................

.. code-block:: php

    #[Dto(linkedEntity: SomeEntity::class, preload: false, validate: false)]
    class SomeDto
    {
        // ...
    }

Reference:
..........

- **linkedEntity** (default: null) - entity class name for preload process.
- **preload** (default: null) - enable/disable entity preload. Overload global configuration if not null.
- **validate** (default: null) - enable/disable DTO validation. Overload global configuration if not null.

