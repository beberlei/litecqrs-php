<?php

namespace LiteCQRS\Plugin\Doctrine\CommandHandler;

use LiteCQRS\CommandHandler\CommandHandlerInterface;
use LiteCQRS\Command;

use Exception;

use Doctrine\ORM\EntityManager;

class OrmTransactionalHandler implements CommandHandlerInterface
{
    private $entityManager;
    private $next;

    public function __construct(EntityManager $entityManager, CommandHandlerInterface $next)
    {
        $this->entityManager = $entityManager;
        $this->next = $next;
    }

    public function handle(Command $command)
    {
        $this->entityManager->beginTransaction();

        try {
            $this->next->handle($command);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch(Exception $e) {
            $this->entityManager->rollBack();
            throw $e;
        }
    }
}

