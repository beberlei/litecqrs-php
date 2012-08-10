<?php

namespace LiteCQRS\Plugin\Doctrine\CommandHandler;

use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\Bus\MessageInterface;

use Exception;

use Doctrine\DBAL\Connection;

class DbalTransactionalHandler implements MessageHandlerInterface
{
    private $conn;
    private $next;

    public function __construct(Connection $conn, MessageHandlerInterface $next)
    {
        $this->conn = $conn;
        $this->next = $next;
    }

    public function handle(MessageInterface $message)
    {
        $this->conn->beginTransaction();

        try {
            $this->next->handle($message);
            $this->next->commit();
        } catch(Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}

