<?php

namespace Lidskasila\Glow\Plugin\SymfonyBundle;

use Lidskasila\Glow\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GlowBundle extends Bundle
{

	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		$container->addCompilerPass(new HandlerPass(), PassConfig::TYPE_AFTER_REMOVING);
	}
}

