<?php

namespace LiteCQRS\Plugin\Swiftmailer;

use LiteCQRS\Bus\MessageHandlerInterface;

use Swift_Transport_SpoolTransport;
use Swift_Transport;
use Swift_MemorySpool;
use Exception;

/**
 * Only send the contents of a spool when the command
 * or event handler was executed successfully.
 *
 * This handler accepts two SwiftMailer transports. A Spool Transport
 * and the real transport to use for mail sending. To send mails
 * transactionally with this handler, you have inject the same
 * spool transport into your command/event handlers and send messages
 * through it.
 *
 * If you need to process the failed recipients you can extend from this class
 * and overwrite the ``handleFailedRecipients($failedRecipients)`` method.
 *
 * @example
 *
 *    $spoolTransport = new \Swift_Transport_SpoolTransport();
 *    $realTransport = new \Swift_Transport_MailTransport();
 *
 *    $proxyFactory = function($handler) use ($spoolTransport, $realTransport) {
 *       return new SpoolTransportHandler($spoolTransport, $realTransport, $handler);
 *    }
 *
 *    $myCommandHandler = new MyHandler($spoolTransport);
 *
 * If you combine this handler with a transactional handler such as Doctrine DBAL/ORM
 * you should let this one be the outer handler. That way, if your database transaction
 * fails, then no mails will be sent.
 *
 * @example
 *
 *    $proxyFactory = function($handler) use ($st, $rt, $conn) {
 *        return new SpoolTransportHandler($st, $rt,
 *            new DbalTransactionalHandler($conn, $handler)
 *        );
 *    }
 */
class SpoolTransportHandler implements MessageHandlerInterface
{
    private $next;
    private $spoolTransport;
    private $realTransport;

    public function __construct(Swift_Transport_SpoolTransport $spoolTransport,
        Swift_Transport $realTransport,
        MessageHandlerInterface $next)
    {
        $this->spoolTransport = $spoolTransport;
        $this->realTransport  = $realTransport;
        $this->next           = $next;
    }

    public function handle($message)
    {
        try {
            $spool = new Swift_MemorySpool();
            $this->spoolTransport->setSpool($spool);
            $this->next->handle($message);

            $failedRecipients = null;
            $spool->flushQueue($this->realTransport, $failedRecipients);
            $this->handleFailedRecipients($failedRecipients);
        } catch (Exception $e) {
            $spool = new Swift_MemorySpool();
            $this->spoolTransport->setSpool($spool);
            throw $e;
        }
    }

    protected function handleFailedRecipients($failedRecipients)
    {
    }
}
