<?php
namespace LiteCQRS;

/**
 * Aggregate Root objects are the primary objects of your domain.
 *
 * In context of the CQRS, the aggregate roots contain all
 * the events were applied during the current transaction
 * and allow other objects to access these events and store
 * them.
 */
interface AggregateRootInterface extends EventProviderInterface
{
    public function loadFromHistory(array $events);
}
