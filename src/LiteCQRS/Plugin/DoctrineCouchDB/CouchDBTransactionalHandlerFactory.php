<?php

namespace LiteCQRS\Plugin\DoctrineCouchDB;

use LiteCQRS\Bus\MessageHandlerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CouchDBTransactionalHandlerFactory
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {

    }

    public function __invoke(MessageHandlerInterface $handler)
    {
        return new CouchDBTransactionalHandler($handler, $this->manager);
    }
}

