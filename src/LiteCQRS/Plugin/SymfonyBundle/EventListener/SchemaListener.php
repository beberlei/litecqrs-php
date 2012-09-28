<?php

namespace LiteCQRS\Plugin\SymfonyBundle\EventListener;

use LiteCQRS\Plugin\Doctrine\EventStore\TableEventStore;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class SchemaListener
{
    public function __construct(TableEventStore $store)
    {
        $this->store = $store;
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        $this->store->addEventsToSchema($args->getSchema());
    }
}
