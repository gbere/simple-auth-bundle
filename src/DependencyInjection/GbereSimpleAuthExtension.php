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
    private const TESTING_ROUTES = [
        ['path' => '^/gbere-auth-test-role-admin', 'role' => 'ROLE_ADMIN'],
        ['path' => '^/gbere-auth-test-role-user', 'role' => 'ROLE_USER'],
    ];

    /** @var array|null */
    private $securityConf;

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
            $this->securityConf['access_control'] = self::TESTING_ROUTES;
        }

        $this->updateSecurityConfig($container);
    }

    private function updateSecurityConfig(ContainerBuilder $container): void
    {
        if (null === $this->securityConf) {
            return;
        }

        $extensionConfigsRefl = new \ReflectionProperty(ContainerBuilder::class, 'extensionConfigs');
        $extensionConfigsRefl->setAccessible(true);
        $extensionConfigs = $extensionConfigsRefl->getValue($container);

        foreach ($this->securityConf as $section => $configs) {
            if (isset($extensionConfigs['security'][0][$section])) {
                $extensionConfigs['security'][0][$section] = array_merge($configs, $extensionConfigs['security'][0][$section]);
            } else {
                $extensionConfigs['security'][0][$section] = $configs;
            }
        }

        $extensionConfigsRefl->setValue($container, $extensionConfigs);
    }
}
