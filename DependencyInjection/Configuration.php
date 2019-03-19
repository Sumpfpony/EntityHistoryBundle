<?php

namespace Sumpfpony\EntityHistoryBundle\DependencyInjection;

use Sumpfpony\EntityHistoryBundle\StoreAdapter\DoctrineAdapter;
use Sumpfpony\EntityHistoryBundle\StoreAdapter\MonologStoreAdapter;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('entity_history');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->arrayNode('adapter')
                    #->defaultValue(['class' => MonologStoreAdapter::class])
                    ->children()
                        ->scalarNode('class')->defaultValue(MonologStoreAdapter::class)->end()
                        ->arrayNode('options')
                            ->defaultValue([])
                            ->variablePrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('classes')
                    ->prototype('array')->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
}
