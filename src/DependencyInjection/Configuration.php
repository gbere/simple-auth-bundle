<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DependencyInjection;

use Gbere\SimpleAuth\Entity\User;
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
                    ->children()
                        ->scalarNode('entity')->defaultValue(User::class)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
