<?php

namespace LiteCQRS\Plugin\Doctrine;

use Doctrine\ORM\EntityManager;
use LiteCQRS\Plugin\Doctrine\MessageHandler\OrmTransactionalHandler;

class OrmHandlerFactory
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke($handler)
    {
        return new OrmTransactionalHandler($this->entityManager, $handler);
    }
}

