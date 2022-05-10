<?php

namespace Becklyn\Security\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-27
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder('becklyn_security');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('secret')
            ->isRequired()
            ->end()
            ->arrayNode('reset_password')
            ->children()
            ->scalarNode('route')
            ->defaultValue('')
            ->end()
            ->scalarNode('email_subject')
            ->defaultValue('')
            ->end()
            ->scalarNode('email_from')
            ->defaultValue('')
            ->end()
            ->scalarNode('request_expiration_minutes')
            ->defaultValue(60)
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
