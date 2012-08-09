<?php
namespace LiteCQRS;

interface AggregateRoot
{
    public function getAppliedEvents();
    public function loadFromHistory(array $events);
}


