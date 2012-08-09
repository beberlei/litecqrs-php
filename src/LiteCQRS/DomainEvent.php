<?php
namespace LiteCQRS;

interface DomainEvent
{
    public function getEventName();
}

