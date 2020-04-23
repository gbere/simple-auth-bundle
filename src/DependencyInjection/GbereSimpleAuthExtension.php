<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DependencyInjection;

use Exception;
use Gbere\SimpleAuth\Security\LoginFormAuthenticator;
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
    private const SECURITY_PROVIDER_NAME = 'gbere_auth_main_provider';
    private const SECURITY_FIREWALL_NAME = 'gbere_auth_main_firewall';

    /** @var array|null */
    private $securityConfig;
    /** @var array|null */
    private $config;

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
        $configs = $container->getExtensionConfig($this->getAlias());
        $this->config = $this->processConfiguration(new Configuration(), $configs);

        if ('test' === $container->getParameter('kernel.environment')) {
            $this->securityConfig['access_control'] = self::TESTING_ROUTES;
        }

        $this->addEncodersSection();
        $this->addProvidersSection();
        $this->addFirewallSection();

        $this->updateSecurityConfig($container);
    }

    private function addEncodersSection(): void
    {
        if (isset($this->config['user'])) {
            $this->securityConfig['encoders'] = [
                $this->config['user']['entity'] => [
                    'algorithm' => $this->config['user']['encoder_algorithm'],
                ],
                // TODO:
                'Gbere\SimpleAuth\Entity\AdminUser' => [
                    'algorithm' => 'auto',
                ],
            ];
        }
    }

    private function addProvidersSection(): void
    {
        if (isset($this->config['user'])) {
            $this->securityConfig['providers'] = [
                self::SECURITY_PROVIDER_NAME => [
                    'entity' => [
                        'class' => $this->config['user']['entity'],
                        'property' => 'email',
                    ],
                ],
            ];
        }
    }

    private function addFirewallSection(): void
    {
        $this->securityConfig['firewalls'] = [
            self::SECURITY_FIREWALL_NAME => [
                'anonymous' => 'lazy',
                'provider' => self::SECURITY_PROVIDER_NAME,
                'guard' => [
                    'authenticators' => [LoginFormAuthenticator::class],
                ],
                'logout' => [
                    'path' => 'gbere_auth_logout',
                ],
            ],
        ];

        if (isset($this->config['remember_me_lifetime']) && null != $this->config['remember_me_lifetime']) {
            $this->securityConfig['firewalls'][self::SECURITY_FIREWALL_NAME]['remember_me'] = [
                'secret' => '%kernel.secret%',
                'lifetime' => $this->config['remember_me_lifetime'],
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
                // gbere_auth_main_firewall must be inserted after the firewall->dev
                if ('firewalls' === $section) {
                    $extensionConfigs['security'][0][$section] = array_merge($extensionConfigs['security'][0][$section], $configs);
                } else {
                    $extensionConfigs['security'][0][$section] = array_merge($configs, $extensionConfigs['security'][0][$section]);
                }
            } else {
                $extensionConfigs['security'][0][$section] = $configs;
            }
        }

        $extensionConfigsRefl->setValue($container, $extensionConfigs);
    }
}
