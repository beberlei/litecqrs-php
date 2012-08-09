<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\AggregateRootInterface;

class SimpleIdentityMap implements IdentityMapInterface
{
    private $map = array();
    public function add(AggregateRootInterface $object)
    {
        $this->map[spl_object_hash($object)] = $object;
    }
    public function all()
    {
        return array_values($this->map);
    }
}

