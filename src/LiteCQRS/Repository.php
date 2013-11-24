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
    public function save(AggregateRoot $object);
}
