<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GbereSimpleAuthExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ('test' === $container->getParameter('kernel.environment')) {
            // https://github.com/symfony/symfony/issues/24461#issuecomment-355839669
            $extensionConfigsRefl = new \ReflectionProperty(ContainerBuilder::class, 'extensionConfigs');
            $extensionConfigsRefl->setAccessible(true);
            $extensionConfigs = $extensionConfigsRefl->getValue($container);
            $extensionConfigs['security'][0]['access_control'] = array_merge(
                [
                    ['path' => '^/gbere-auth-test-role-admin', 'role' => 'ROLE_ADMIN'],
                    ['path' => '^/gbere-auth-test-role-user', 'role' => 'ROLE_USER'],
                ],
                $extensionConfigs['security'][0]['access_control']
            );

            $extensionConfigsRefl->setValue($container, $extensionConfigs);
        }
    }
}
