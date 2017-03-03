<?php

namespace Lidskasila\Glow\Plugin\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

	public function getConfigTreeBuilder()
	{
		$tb = new TreeBuilder();

		$tb
			->root('lite_cqrs')
			->children()
			->booleanNode('monolog')->defaultTrue()->end()
			->end();

		return $tb;
	}
}
