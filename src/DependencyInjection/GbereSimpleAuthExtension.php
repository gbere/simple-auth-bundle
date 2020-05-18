<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DependencyInjection;

use Exception;
use Gbere\SimpleAuth\Repository\AdminUserRepository;
use Gbere\SimpleAuth\Repository\UserRepository;
use Gbere\SimpleAuth\Security\Constant;
use Gbere\SimpleAuth\Security\LoginFormAuthenticator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GbereSimpleAuthExtension extends Extension implements PrependExtensionInterface
{
    /** @var array|null */
    private $securityConfig;
    /** @var array|null */
    private $twigConfig;
    /** @var array|null */
    private $config;

    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $definition = $container->getDefinition(UserRepository::class);
        $definition->setArgument(1, new Reference($this->config['user']['entity']));

        $definition = $container->getDefinition(AdminUserRepository::class);
        $definition->setArgument(1, new Reference($this->config['admin_user']['entity']));

        $container->setParameter('simple_auth_sender_email', $this->config['sender']['email']);
        $container->setParameter('simple_auth_sender_name', $this->config['sender']['name']);
        $container->setParameter('simple_auth_confirm_registration', $this->config['confirm_registration']);
        $container->setParameter('simple_auth_remember_me', $this->config['remember_me_lifetime'] ? true : false);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $this->config = $this->processConfiguration(new Configuration(), $configs);

        if ('test' === $container->getParameter('kernel.environment')) {
            $this->addSecurityTestingRoutesConfig();
        }

        $this->addSecurityEncodersConfig();
        $this->addSecurityProvidersConfig();
        $this->addSecurityFirewallConfig();
        $this->updateSecurityExtensionConfig($container);

        $this->addTwigGlobalsConfig();
        $this->updateTwigExtensionConfig($container);
    }

    private function addSecurityTestingRoutesConfig(): void
    {
        $this->securityConfig['access_control'] = constant::TESTING_ROUTES;
    }

    private function addSecurityEncodersConfig(): void
    {
        if (isset($this->config['user'])) {
            $this->securityConfig['encoders'] = [
                $this->config['user']['entity'] => [
                    'algorithm' => $this->config['user']['encoder_algorithm'],
                ],
                $this->config['admin_user']['entity'] => [
                    'algorithm' => $this->config['admin_user']['encoder_algorithm'],
                ],
            ];
        }
    }

    private function addSecurityProvidersConfig(): void
    {
        if (isset($this->config['user'])) {
            $this->securityConfig['providers'] = [
                Constant::PROVIDER_NAME => [
                    'entity' => [
                        'class' => $this->config['user']['entity'],
                        'property' => 'email',
                    ],
                ],
            ];
        }
    }

    private function addSecurityFirewallConfig(): void
    {
        $this->securityConfig['firewalls'] = [
            Constant::FIREWALL_NAME => [
                'anonymous' => 'lazy',
                'provider' => Constant::PROVIDER_NAME,
                'guard' => [
                    'authenticators' => [LoginFormAuthenticator::class],
                ],
                'logout' => [
                    'path' => 'simple_auth_logout',
                ],
            ],
        ];

        if (isset($this->config['remember_me_lifetime']) && null != $this->config['remember_me_lifetime']) {
            $this->securityConfig['firewalls'][Constant::FIREWALL_NAME]['remember_me'] = [
                'secret' => '%kernel.secret%',
                'lifetime' => $this->config['remember_me_lifetime'],
            ];
        }
    }

    private function updateSecurityExtensionConfig(ContainerBuilder $container): void
    {
        if (null === $this->securityConfig) {
            return;
        }

        $extensionConfigsRefl = new \ReflectionProperty(ContainerBuilder::class, 'extensionConfigs');
        $extensionConfigsRefl->setAccessible(true);
        $extensionConfigs = $extensionConfigsRefl->getValue($container);

        foreach ($this->securityConfig as $section => $configs) {
            if (isset($extensionConfigs['security'][0][$section])) {
                if ('firewalls' === $section) {
                    // added after firewall->dev and before firewall->main
                    if (isset($extensionConfigs['security'][0][$section]['main'])) {
                        $configs['main'] = $extensionConfigs['security'][0][$section]['main'];
                        unset($extensionConfigs['security'][0][$section]['main']);
                    }
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

    private function addTwigGlobalsConfig(): void
    {
        $this->twigConfig['globals']['simple_auth_logo'] = $this->config['style']['logo'];
        $this->twigConfig['globals']['simple_auth_accent_color'] = '#'.$this->config['style']['accent_color'];
    }

    private function updateTwigExtensionConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('twig', $this->twigConfig);
    }
}
