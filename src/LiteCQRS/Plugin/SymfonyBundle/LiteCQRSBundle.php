<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LiteCQRSBundle extends Bundle
{

	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		$container->addCompilerPass(new HandlerPass(), PassConfig::TYPE_AFTER_REMOVING);
	}
}

