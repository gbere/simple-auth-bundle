<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DependencyInjection;

use Gbere\SimpleAuth\Entity\AdminUser;
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
                ->arrayNode('sender')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('email')->defaultValue('sender@email.com')->end()
                        ->scalarNode('name')->defaultValue('Sender Name')->end()
                    ->end()
                ->end()
                ->arrayNode('user')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity')->defaultValue(User::class)->end()
                        ->scalarNode('encoder_algorithm')->defaultValue('auto')->end()
                    ->end()
                ->end()
                ->arrayNode('admin_user')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity')->defaultValue(AdminUser::class)->end()
                        ->scalarNode('encoder_algorithm')->defaultValue('auto')->end()
                    ->end()
                ->end()
                ->integerNode('remember_me_lifetime')->defaultValue(null)->end()
                ->booleanNode('confirm_registration_by_email')->defaultValue(true)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
