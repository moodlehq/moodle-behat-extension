<?php

namespace Moodle\BehatExtension\Gherkin;

use Behat\Gherkin\Gherkin,
    Symfony\Component\Yaml\Yaml;

/**
 * Gherkin extension to load multiple features folders
 *
 * Moodle has multiple features folders across all Moodle
 * components (including 3rd party plugins) this extension loads
 * a YAML file from Moodle codebase describing the available
 * components with features
 *
 * @uses      Symfony\Component\Yaml\Yaml
 * @author    David Monllaó <david.monllao@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleGherkin extends Gherkin
{

    /**
     * Moodle config file contents
     * @var array
     */
    protected $moodleConfig;

    /**
     * Loads and parses the Moodle config file
     *
     * @param string  $moodleConfigPath
     */
    public function __construct($moodleConfigPath)
    {
        $this->moodleConfig = Yaml::parse($moodleConfigPath);
    }

    /**
     * Multiple features folders loader
     *
     * Delegates load execution to parent including filters management
     *
     * @param mixed $resource Resource to load
     * @param array $filters  Additional filters
     * @return array
     */
    public function load($resource, array $filters = array())
    {

        // If a resource is specified don't overwrite the parent behaviour.
        if ($resource != '') {
            return parent::load($resource, $filters);
        }

        if (!is_array($this->moodleConfig)) {
            throw new RuntimeException('The moodledata config.yml file can not be read by behat, ensure that you specified it\'s path in behat.yml');
        }

        // Loads all the features files of each Moodle component.
        $features = array();
        if (!empty($this->moodleConfig['features'])) {
            foreach ($this->moodleConfig['features'] as $path) {
                $features = array_merge($features, parent::load($path, $filters));
            }
        }
        return $features;
    }

}
