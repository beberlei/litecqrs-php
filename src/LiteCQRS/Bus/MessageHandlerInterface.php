<?php

namespace LiteCQRS\Bus;

interface MessageHandlerInterface
{
    public function handle(MessageInterface $message);
}

