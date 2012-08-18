<?php

namespace LiteCQRS\Plugin\DoctrineCouchDB;

use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\Bus\MessageInterface;

use Doctrine\Common\Persistence\ObjectManager;

class CouchDBTransactionalHandler implements MessageHandlerInterface
{
    private $next;

    public function __construct(MessageHandlerInterface $next, ObjectManager $manager)
    {
        $this->next    = $next;
        $this->manager = $manager;
    }

    public function handle(MessageInterface $message)
    {
        $this->next->handle($message);
        $this->manager->flush();
    }
}

