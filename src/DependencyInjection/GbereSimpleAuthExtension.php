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
    private $securityConfig;
    /** @var array|null */
    private $prependConfig;

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
            $this->securityConfig['access_control'] = self::TESTING_ROUTES;
        }

        $this->prependConfig = $container->getExtensionConfig($this->getAlias());
        $this->addEncodersSection();
        $this->updateSecurityConfig($container);
    }

    private function addEncodersSection(): void
    {
        if (isset($this->prependConfig[0]['user'])) {
            $this->securityConfig['encoders'] = [
                $this->prependConfig[0]['user']['entity'] => [
                    'algorithm' => $this->prependConfig[0]['user']['encoder_algorithm'],
                ],
                // TODO:
                'Gbere\SimpleAuth\Entity\AdminUser' => [
                    'algorithm' => 'auto',
                ],
            ];
        }
    }

    private function updateSecurityConfig(ContainerBuilder $container): void
    {
        if (null === $this->securityConfig) {
            return;
        }

        $extensionConfigsRefl = new \ReflectionProperty(ContainerBuilder::class, 'extensionConfigs');
        $extensionConfigsRefl->setAccessible(true);
        $extensionConfigs = $extensionConfigsRefl->getValue($container);

        foreach ($this->securityConfig as $section => $configs) {
            if (isset($extensionConfigs['security'][0][$section])) {
                $extensionConfigs['security'][0][$section] = array_merge($configs, $extensionConfigs['security'][0][$section]);
            } else {
                $extensionConfigs['security'][0][$section] = $configs;
            }
        }

        $extensionConfigsRefl->setValue($container, $extensionConfigs);
    }
}
