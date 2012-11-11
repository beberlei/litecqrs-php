<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\AggregateRoot;
use LiteCQRS\Plugin\CRUD\Model\Events\ResourceCreatedEvent;
use LiteCQRS\Plugin\CRUD\Model\Events\ResourceUpdatedEvent;
use LiteCQRS\Plugin\CRUD\Model\Events\ResourceDeletedEvent;

use LiteCQRS\DomainEvent;

/**
 * Aggregate Resource Base class that helps you implement
 * CRUD in combination with CQRS. It adds the necessary
 * methods to make the ``CRUDCommandService`` work
 * on aggregate roots of this type.
 *
 * Important: By default this implementation is vulnerable
 * to mass assignment vulnerability. See {@link
 * http://chadmoran.com/posts/mass-assignment-vulnerability-isn-quo-t-just-for-rails}
 * for more details. To fix this issue implement a white
 * list of properties that are allowed to be updated using
 * the {@see getAccessibleProperties()} method.
 */
abstract class AggregateResource extends AggregateRoot
{
    use DomainAsAggregate;
    use CrudCreatable;
    use CrudDeletable;
    use CrudUpdatable;
}

