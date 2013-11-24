<?php

namespace LiteCQRS;

use Rhumsaa\Uuid\Uuid;

interface Repository
{
    /**
     * @param string $className
     * @return AggregateRoot
     */
    public function find($className, Uuid $uuid);

    /**
     * @return void
     */
    public function add(AggregateRoot $object);

    /**
     * @return void
     */
    public function remove(AggregateRoot $object);
}
