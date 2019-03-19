<?php

namespace Sumpfpony\EntityHistoryBundle\DependencyInjection;

use Sumpfpony\EntityHistoryBundle\Registry\Catalogue;
use Sumpfpony\EntityHistoryBundle\Registry\Factory;
use Sumpfpony\EntityHistoryBundle\Registry\Registry;
use Sumpfpony\EntityHistoryBundle\StoreAdapter\DoctrineAdapter;
use Sumpfpony\EntityHistoryBundle\StoreAdapter\StoreAdapterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EntityHistoryExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');


        // define default store adapter from config
        $container->setAlias(StoreAdapterInterface::class, $config['adapter']['class']);

        if(isset($config['adapter']['options'])) {
            $defintion = $container->getDefinition($config['adapter']['class']);
            $nameConverter = new CamelCaseToSnakeCaseNameConverter();
            foreach($config['adapter']['options'] as $key => $value) {
                $defintion->addMethodCall('set' . ucFirst($nameConverter->denormalize($key)), [$value]);
            }
        }


        /*if($config['adapter']['class'] === DoctrineAdapter::class)
        {
            $docgtrineAdapter = $container->getDefinition(DoctrineAdapter::class);
            $docgtrineAdapter->addMethodCall('setEntity', [$config['adapter']['options']['entity']]);
        }*/



        // register classes
        if($config['classes']) {

            $catalogue = $container->getDefinition(Catalogue::class);

            foreach ($config['classes'] as $class => $classConfig)
            {
                $registry = new Definition();
                $registry
                    ->setClass(Registry::class)
                    ->setFactory([ Factory::class, "create"])
                    ->addArgument($class);

                $catalogue->addMethodCall('addRegistry', [$registry]);
            }
        }

    }
}
