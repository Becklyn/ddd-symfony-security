<?php declare(strict_types=1);

namespace Becklyn\Security\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-22
 */
class BecklynSecurityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../resources/config')
        );
        $loader->load('services.yml');

        $definition = $container->getDefinition('becklyn_security.infrastructure.application.symfony.mailer_notify_password_reset');
        $definition->replaceArgument(2, $config['reset_password']['route']);
        $definition->replaceArgument(3, $config['reset_password']['email_from']);
        $definition->replaceArgument(4, $config['reset_password']['email_subject']);

        $definition = $container->getDefinition('becklyn_security.infrastructure.application.doctrine.find_user_for_password_reset');
        $definition->replaceArgument(1, $config['reset_password']['request_expiration_minutes']);
        $container->setParameter('reset_password.request_expiration_minutes', $config['reset_password']['request_expiration_minutes']);

        $definition = $container->getDefinition('becklyn_security.domain.hash_password_reset_token');
        $definition->replaceArgument(0, $config['secret']);
    }
}
