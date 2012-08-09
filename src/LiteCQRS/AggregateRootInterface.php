<?php
namespace LiteCQRS;

interface AggregateRootInterface
{
    public function getAppliedEvents();
    public function loadFromHistory(array $events);
}


