<?php

namespace LiteCQRS\Plugin\Doctrine\MessageHandler;

use LiteCQRS\Bus\MessageHandlerInterface;

use Doctrine\Common\Persistence\ObjectManager;

class DoctrineTransactionalHandler implements MessageHandlerInterface
{
    private $next;
    private $manager;

    public function __construct(MessageHandlerInterface $next, ObjectManager $manager)
    {
        $this->next    = $next;
        $this->manager = $manager;
    }

    public function handle($message)
    {
        $this->next->handle($message);
        $this->manager->flush();
    }
}
