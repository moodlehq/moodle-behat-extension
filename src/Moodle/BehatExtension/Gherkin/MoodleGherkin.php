<?php

namespace Moodle\BehatExtension\Gherkin;

use Behat\Gherkin\Gherkin,
    Symfony\Component\Yaml\Yaml;

/**
 * Gherkin extension to load multiple features folders
 *
 * Moodle has multiple features folders across all Moodle
 * components (including 3rd party plugins) this extension loads
 * a file from Moodle codebase describing the available features
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
     * Loads and parses the specified Moodle config file
     *
     * Moodle\BehatExtension\Extension:
     *   config_file_path: /I/am/the/file/path
     *
     * @param string  $moodleConfigPath
     */
    public function __construct($moodleConfigPath)
    {
        $this->moodleConfig = Yaml::parse($moodleConfigPath);
    }

    /**
     * Multiple features folders loading
     *
     * Delegates load execution to parent including filters management
     *
     * @param mixed $resource Resource to load
     * @param array $filters  Additional filters
     * @return array
     */
    public function load($resource, array $filters = array())
    {

        // Get moodle features and
        $moodledata = Yaml::parse($this->moodleConfigPath);

        $features = array();
        foreach ($moodledata['features'] as $path) {
            $features = array_merge($features, parent::load($path, $filters));
        }
        return $features;
    }

}