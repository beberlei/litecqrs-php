<?php

namespace Lidskasila\Glow\Plugin\Monolog;

use Monolog\Logger;

class MonologLoggerFactory
{

	private $logger;

	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
	}

	public function __invoke($handler)
	{
		return new MonologDebugLogger($handler, $this->logger);
	}
}
