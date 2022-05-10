<?php

namespace Becklyn\Security;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-22
 */
class BecklynSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $mappings = [
            realpath(__DIR__ . '/../resources/config/doctrine-mapping') => 'Becklyn\Security\Infrastructure\Domain\Doctrine',
        ];

        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
    }
}
