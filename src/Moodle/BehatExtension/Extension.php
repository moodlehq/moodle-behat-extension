<?php

namespace Moodle\BehatExtension;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Behat\Behat\Extension\ExtensionInterface;


/**
 * Behat extension for moodle
 *
 * Provides multiple features directory loading (Gherkin\Loader\MoodleFeaturesSuiteLoader
 */
class Extension implements ExtensionInterface
{

    /**
     * Loads moodle specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
        $loader->load('core.xml');

        if (isset($config['config_file_path'])) {
            $container->setParameter('behat.moodle.config_file_path', rtrim($config['config_file_path'], '/'));
        }
    }

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder->
            children()->
                scalarNode('config_file_path')->
                    defaultNull()->
                end()->
            end()->
        end();
    }

    /**
     * Returns compiler passes used by this extension.
     *
     * @return array
     */
    public function getCompilerPasses()
    {
        return array();
    }
}