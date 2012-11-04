<?php

namespace Moodle\BehatExtension\Context;

use Behat\Behat\Context\BehatContext,
    Symfony\Component\Yaml\Yaml;

/**
 * Moodle contexts loader
 *
 * It gathers all the available steps definitions reading the
 * Moodle configuration file
 *
 * @copyright  2012 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleContext extends BehatContext
{

    /**
     * Moodle features and steps definitions list
     * @var array
     */
    protected $moodleConfig;

    /**
     * Includes all the specified Moodle subcontexts
     * @param array $parameters
     */
    public function setMoodleConfig($parameters)
    {
        $this->moodleConfig = $parameters;

        if (!is_array($this->moodleConfig)) {
            throw new RuntimeException('There are no Moodle features nor steps definitions');
        }

        // Using the key as context identifier.
        if (!empty($this->moodleConfig['steps_definitions'])) {
            foreach ($this->moodleConfig['steps_definitions'] as $classname => $path) {
                if (file_exists($path)) {
                    require_once($path);
                    $this->useContext($classname, new $classname());
                }
            }
        }
    }
}
