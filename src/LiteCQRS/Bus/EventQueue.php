<?php

namespace LiteCQRS\Bus;

/**
 * Allows access to all events that commands have triggered.
 */
interface EventQueue
{
    /**
     * Dequeue all events from the queue and return them in order.
     *
     * @return array<object>
     */
    public function dequeueAllEvents();
}
