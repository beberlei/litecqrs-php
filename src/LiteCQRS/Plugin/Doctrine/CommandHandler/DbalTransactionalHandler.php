<?php

namespace LiteCQRS\Plugin\Doctrine\CommandHandler;

use LiteCQRS\CommandHandler\CommandHandlerInterface;
use LiteCQRS\Command;

use Exception;

use Doctrine\DBAL\Connection;

class DbalTransactionalHandler implements CommandHandlerInterface
{
    private $conn;
    private $next;

    public function __construct(Connection $conn, CommandHandlerInterface $next)
    {
        $this->conn = $conn;
        $this->next = $next;
    }

    public function handle(Command $command)
    {
        $this->conn->beginTransaction();

        try {
            $this->next->handle($command);
            $this->next->commit();
        } catch(Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}

