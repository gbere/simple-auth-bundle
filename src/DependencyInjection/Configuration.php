<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('gbere_simple_auth');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('user')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity')->cannotBeEmpty()->end()
                        ->scalarNode('encoder_algorithm')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->scalarNode('remember_me_lifetime')->defaultValue(null)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
