<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;

class LiteCQRSBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new HandlerPass());
    }
}

