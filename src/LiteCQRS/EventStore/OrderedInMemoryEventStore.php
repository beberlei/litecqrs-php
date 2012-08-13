<?php

namespace LiteCQRS\EventStore;

class OrderedInMemoryEventStore extends InMemoryEventStore
{
    protected function sort($events)
    {
        usort($events, function($a, $b) {
            $ad = $a->getMessageHeader()->date;
            $bd = $b->getMessageHeader()->date;

            if ($ad == $bd) {
                return $ad->format('u') > $bd->format('u') ? 1 : -1;
            } else if ($ad > $bd) {
                return 1;
            } else {
                return -1;
            }
        });

        return $events;
    }
}

