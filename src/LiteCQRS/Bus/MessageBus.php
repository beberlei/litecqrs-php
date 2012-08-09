<?php
namespace LiteCQRS\Bus;

interface MessageBus
{
    public function handle($message);
}
