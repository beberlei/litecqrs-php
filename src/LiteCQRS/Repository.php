<?php

namespace LiteCQRS;

use Rhumsaa\Uuid\Uuid;

interface Repository
{
    /**
     * @return AggregateRoot
     */
    public function find(Uuid $uuid);

    /**
     * @return void
     */
    public function add(AggregateRoot $object);

    /**
     * @return void
     */
    public function remove(AggregateRoot $object);
}
