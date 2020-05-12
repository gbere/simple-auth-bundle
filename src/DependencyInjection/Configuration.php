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
                        // TODO: check if it is a valid email
                        ->scalarNode('email')->defaultValue('sender@email.com')->end()
                        ->scalarNode('name')->defaultValue('Sender Name')->end()
                    ->end()
                ->end()
                ->arrayNode('user')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity')->defaultValue(User::class)->info('If you want to extend the entity, the entity name must also be "User". Example: App/Entity/User')->end()
                        ->scalarNode('encoder_algorithm')->defaultValue('auto')->end()
                    ->end()
                ->end()
                ->arrayNode('admin_user')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity')->defaultValue(AdminUser::class)->info('If you want to extend the entity, the entity name must also be "AdminUser". Example: App/Entity/AdminUser')->end()
                        ->scalarNode('encoder_algorithm')->defaultValue('auto')->end()
                    ->end()
                ->end()
                ->arrayNode('style')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarnode('form_logo')->defaultValue('default')->info('Set custom path image, disable with ~ char or default demo image')->end()
                        ->scalarnode('accent_color')->defaultValue('0088aa')->info('Set color in RGB hexadecimal without the #')->end()
                    ->end()
                ->end()
                ->integerNode('remember_me_lifetime')->defaultValue(null)->end()
                ->booleanNode('confirm_registration')->defaultValue(true)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
