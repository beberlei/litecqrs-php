<?php

namespace LiteCQRS\Plugin\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\Plugin\Doctrine\MessageHandler\DoctrineTransactionalHandler;

class DoctrineTransactionalHandlerFactory
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(MessageHandlerInterface $handler)
    {
        return new DoctrineTransactionalHandler($handler, $this->manager);
    }
}
