<?php

namespace LiteCQRS\Bus\IdentityMap;

use LiteCQRS\EventProviderInterface;

/**
 * Identity map tracks all aggregate roots.
 *
 * The CommandBus uses the identity map to pass over all
 * the newly applied events of all the registered aggregate roots
 * to the {@see EventStoreInterface}
 */
interface IdentityMapInterface
{
    public function add(EventProviderInterface $object);
    public function all();
    public function getAggregateId(EventProviderInterface $object);
}
