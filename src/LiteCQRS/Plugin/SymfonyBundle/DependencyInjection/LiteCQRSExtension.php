<?php

namespace LiteCQRS\Plugin\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LiteCQRSExtension extends Extension
{

	public function load(array $configs, ContainerBuilder $container)
	{
		$config = $this->processConfiguration(new Configuration, $configs);

		$loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.xml');
	}
}

