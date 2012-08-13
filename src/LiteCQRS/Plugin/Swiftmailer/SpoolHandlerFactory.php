<?php

namespace LiteCQRS\Plugin\Swiftmailer;

class SpoolHandlerFactory
{
    private $spoolTransport;
    private $realTransport;

    public function __construct(Swift_Transport_SpoolTransport $spoolTransport, Swift_Transport $realTransport)
    {
        $this->spoolTransport = $spoolTransport;
        $this->realTransport  = $realTransport;
    }

    public function __invoke($handler)
    {
        return new SpoolTransportHandler($this->spoolTransport, $this->realTransport, $handler);
    }
}

