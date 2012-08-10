<?php

namespace LiteCQRS\Plugin\Doctrine\MessageHandler;

use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\Bus\MessageInterface;

use Exception;

use Doctrine\ORM\EntityManager;

class OrmTransactionalHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $next;

    public function __construct(EntityManager $entityManager, MessageHandlerInterface $next)
    {
        $this->entityManager = $entityManager;
        $this->next = $next;
    }

    public function handle(MessageInterface $message)
    {
        $this->entityManager->beginTransaction();

        try {
            $this->next->handle($message);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch(Exception $e) {
            $this->entityManager->rollBack();
            throw $e;
        }
    }
}

