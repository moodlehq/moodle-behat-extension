<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle behat context class resolver.
 *
 * @package    behat
 * @copyright  2016 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodle\BehatExtension\Driver;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;

class MoodleSelenium2Factory implements DriverFactory {
    /**
     * {@inheritdoc}
     */
    public function getDriverName() {
        return 'selenium2';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsJavascript() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder) {
        $builder
            ->children()
                ->scalarNode('browser')
                    ->defaultValue('%mink.browser_name%')
                ->end()
                ->append($this->getCapabilitiesNode())
                ->scalarNode('wd_host')
                    ->defaultValue('http://localhost:4444/wd/hub')
                ->end()
                ->arrayNode('moodle_parameter')
                    ->useAttributeAsKey('key')
                    ->prototype('variable')
                    ->defaultValue('%behat.moodle.parameter%')
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config) {
        if (!class_exists('Moodle\BehatExtension\Driver\MoodleSelenium2Driver')) {
            throw new \RuntimeException(sprintf(
                'Install MinkSelenium2Driver in order to use %s driver.',
                $this->getDriverName()
            ));
        }

        $extraCapabilities = $config['capabilities']['extra_capabilities'];
        unset($config['capabilities']['extra_capabilities']);

        if (getenv('TRAVIS_JOB_NUMBER')) {
            $guessedCapabilities = array(
                'tunnel-identifier' => getenv('TRAVIS_JOB_NUMBER'),
                'build' => getenv('TRAVIS_BUILD_NUMBER'),
                'tags' => array('Travis-CI', 'PHP '.phpversion()),
            );
        } elseif (getenv('JENKINS_HOME')) {
            $guessedCapabilities = array(
                'tunnel-identifier' => getenv('JOB_NAME'),
                'build' => getenv('BUILD_NUMBER'),
                'tags' => array('Jenkins', 'PHP '.phpversion(), getenv('BUILD_TAG')),
            );
        } else {
            $guessedCapabilities = array(
                'tags' => array(php_uname('n'), 'PHP '.phpversion()),
            );
        }

        return new Definition('Moodle\BehatExtension\Driver\MoodleSelenium2Driver', array(
            $config['browser'],
            array_replace($extraCapabilities, $guessedCapabilities, $config['capabilities']),
            $config['wd_host'],
            $config['moodle_parameter']
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCapabilitiesNode() {
        $node = new ArrayNodeDefinition('capabilities');

        $node
            ->addDefaultsIfNotSet()
            ->normalizeKeys(false)
            ->children()
                ->scalarNode('browserName')->defaultValue('firefox')->end()
                ->scalarNode('version')->defaultValue('21')->end()
                ->scalarNode('platform')->defaultValue('ANY')->end()
                ->scalarNode('browserVersion')->defaultValue('9')->end()
                ->scalarNode('browser')->defaultValue('firefox')->end()
                ->scalarNode('ignoreZoomSetting')->defaultValue('false')->end()
                ->scalarNode('name')->defaultValue('Behat feature suite')->end()
                ->scalarNode('deviceOrientation')->defaultValue('portrait')->end()
                ->scalarNode('deviceType')->defaultValue('tablet')->end()
                ->booleanNode('javascriptEnabled')->end()
                ->booleanNode('databaseEnabled')->end()
                ->booleanNode('locationContextEnabled')->end()
                ->booleanNode('applicationCacheEnabled')->end()
                ->booleanNode('browserConnectionEnabled')->end()
                ->booleanNode('webStorageEnabled')->end()
                ->booleanNode('rotatable')->end()
                ->booleanNode('acceptSslCerts')->end()
                ->booleanNode('nativeEvents')->end()
                ->arrayNode('proxy')
                    ->children()
                        ->scalarNode('proxyType')->end()
                        ->scalarNode('proxyAuthconfigUrl')->end()
                        ->scalarNode('ftpProxy')->end()
                        ->scalarNode('httpProxy')->end()
                        ->scalarNode('sslProxy')->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function ($v) {
                            return empty($v);
                        })
                        ->thenUnset()
                    ->end()
                ->end()
                ->arrayNode('firefox')
                    ->children()
                        ->scalarNode('profile')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return !file_exists($v);
                                })
                                ->thenInvalid('Cannot find profile zip file %s')
                            ->end()
                        ->end()
                        ->scalarNode('binary')->end()
                    ->end()
                ->end()
                ->arrayNode('chrome')
                    ->children()
                        ->arrayNode('switches')->prototype('scalar')->end()->end()
                        ->scalarNode('binary')->end()
                        ->arrayNode('extensions')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
                ->arrayNode('extra_capabilities')
                    ->info('Custom capabilities merged with the known ones')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
            ->end();

        return $node;
    }
}
