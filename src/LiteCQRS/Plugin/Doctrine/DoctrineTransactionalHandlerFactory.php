<?php

namespace LiteCQRS\Plugin\DoctrineCouchDB;

use LiteCQRS\Bus\MessageHandlerInterface;
use Doctrine\Common\Persistence\ObjectManager;

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

