<?php

namespace LiteCQRS\Bus;

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
    public function getAggregateId(AggregateRootInterface $object)
    {
        return null;
    }
}

